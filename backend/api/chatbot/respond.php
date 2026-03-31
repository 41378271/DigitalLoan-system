<?php
session_start();
header("Content-Type: application/json");
require_once "../../config/db.php"; // adjust if your path differs

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["success"=>false, "reply"=>"Please log in to use the chatbot."]);
  exit;
}

$user_id = (int)$_SESSION['user_id'];
$role    = $_SESSION['role'] ?? 'borrower';

$msg = trim($_POST['message'] ?? '');
$lc  = strtolower($msg);

function addLinks($text, $role){
  $map = [
    'OPEN_DASHBOARD'     => ['Open Dashboard', '/digital-loan-system/frontend/pages/borrower_dashboard.php'],
    'OPEN_UPLOAD_KYC'    => ['Open Upload KYC', '/digital-loan-system/frontend/pages/upload_kyc.php'],
    'OPEN_APPLY_LOAN'    => ['Open Apply Loan', '/digital-loan-system/frontend/pages/apply_loan.php'],
    'OPEN_MY_LOANS'      => ['Open My Loans', '/digital-loan-system/frontend/pages/my_loans.php'],
    'OPEN_NOTIFICATIONS' => ['Open Notifications', '/digital-loan-system/frontend/pages/notifications.php'],

    'OPEN_ADMIN_DASH'    => ['Open Admin Dashboard', '/digital-loan-system/frontend/pages/admin_dashboard.php'],
    'OPEN_ADMIN_KYC'     => ['Open Admin KYC Review', '/digital-loan-system/frontend/pages/admin_kyc.php'],
    'OPEN_ADMIN_LOANS'   => ['Open Admin Loans', '/digital-loan-system/frontend/pages/admin_loans.php'],
  ];

  if($role === 'borrower'){
    unset($map['OPEN_ADMIN_DASH'], $map['OPEN_ADMIN_KYC'], $map['OPEN_ADMIN_LOANS']);
  } else {
    unset($map['OPEN_DASHBOARD'], $map['OPEN_UPLOAD_KYC'], $map['OPEN_APPLY_LOAN'], $map['OPEN_MY_LOANS']);
  }

  foreach($map as $token => $info){
    $label = $info[0];
    $url   = $info[1];
    $text  = str_replace("[[$token]]", "[LINK:$label|$url]", $text);
  }
  return $text;
}

function has($text, $words) {
  foreach ($words as $w) if (strpos($text, $w) !== false) return true;
  return false;
}

function money($n){
  return number_format((float)$n, 2, '.', ',');
}

function saveChat($conn, $user_id, $role, $message, $is_read){
  $stmt = $conn->prepare("INSERT INTO chat_messages (user_id, role, message, is_read) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("issi", $user_id, $role, $message, $is_read);
  $stmt->execute();
}

if ($msg === '') {
  echo json_encode([
    "success"=>true,
    "reply"=>"Type something 🙂 For example: 'loan status', 'upload kyc', 'notifications', 'calculate loan 50000 12 15%'",
    "suggestions"=>["Upload KYC","Loan status","Notifications","Calculate loan 50000 12 15%"]
  ]);
  exit;
}

/* ✅ Save USER message */
saveChat($conn, $user_id, "user", $msg, 1);

/* --- intent detect --- */
$intent = 'unknown';
if (has($lc, ["hi","hello","hey","good morning","good evening"])) $intent = 'greet';
elseif (has($lc, ["kyc","verification","verify","document","id","passport","license","proof"])) $intent = 'kyc';
elseif (has($lc, ["notification","notifications","alert","unread"])) $intent = 'notifications';
elseif (has($lc, ["apply","loan","borrow","request","amount","term"])) $intent = 'loan';
elseif (has($lc, ["status","approved","rejected","pending","loan status"])) $intent = 'status';
elseif (has($lc, ["report","stats","analytics","dashboard"])) $intent = 'admin_reports';
elseif (has($lc, ["approve","reject","review","kyc review"])) $intent = 'admin_kyc';
elseif (has($lc, ["disburse","disbursement","payout"])) $intent = 'admin_disburse';
elseif (has($lc, ["calculate","calculator","interest","%"])) $intent = 'calc';

$reply = "I didn’t understand that yet. Try: 'upload kyc', 'loan status', 'notifications', or 'calculate loan 50000 12 15%'.";
$suggestions = ["Upload KYC","Loan status","Notifications","Calculate loan 50000 12 15%"];

/* ✅ REAL DB answers */
if ($role === 'borrower') {

  if ($intent === 'greet') {
    $name = $_SESSION['name'] ?? 'there';
    $reply = "Hi $name 🙂 I’m your Loan Assistant. Ask me about KYC, loans, status, or notifications.";
    $suggestions = ["Upload KYC","Loan status","Notifications","Calculate loan 50000 12 15%"];
  }

  elseif ($intent === 'kyc') {
    // latest KYC
    $stmt = $conn->prepare("SELECT status, doc_type, uploaded_at, admin_comment FROM kyc_documents WHERE user_id=? ORDER BY uploaded_at DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $kyc = $stmt->get_result()->fetch_assoc();

    if (!$kyc) {
      $reply = "You have not uploaded KYC yet.\nOpen here: [[OPEN_UPLOAD_KYC]]";
      $suggestions = ["Upload KYC","How long review?","What docs needed?","Notifications"];
    } else {
      $reply = "Your latest KYC:\n- Type: {$kyc['doc_type']}\n- Status: {$kyc['status']}\n- Uploaded: {$kyc['uploaded_at']}";
      if (!empty($kyc['admin_comment'])) $reply .= "\n- Comment: {$kyc['admin_comment']}";
      $reply .= "\n\nOpen page: [[OPEN_DASHBOARD]]";
      $suggestions = ["Open Dashboard","Upload KYC","Notifications","Loan status"];
    }
  }

  elseif ($intent === 'notifications') {
    $stmt = $conn->prepare("SELECT COUNT(*) c FROM notifications WHERE user_id=? AND is_read=0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $count = (int)$stmt->get_result()->fetch_assoc()['c'];

    $reply = "You have $count unread notification(s).\nOpen: [[OPEN_NOTIFICATIONS]]";
    $suggestions = ["Open Notifications","Mark as read","Why did I get this?","Back to dashboard"];
  }

  elseif ($intent === 'loan' || $intent === 'status') {
    // latest loan
    $stmt = $conn->prepare("SELECT amount, term_months, status, created_at FROM loans WHERE user_id=? ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $loan = $stmt->get_result()->fetch_assoc();

    if (!$loan) {
      $reply = "You don’t have any loan applications yet.\nApply here: [[OPEN_APPLY_LOAN]]";
      $suggestions = ["Open Apply Loan","KYC status","Notifications","Upload KYC"];
    } else {
      $reply =
        "Latest loan:\n" .
        "- Amount: " . money($loan['amount']) . "\n" .
        "- Term: {$loan['term_months']} months\n" .
        "- Status: {$loan['status']}\n" .
        "- Date: {$loan['created_at']}\n\n" .
        "Open loans page: [[OPEN_MY_LOANS]]";
      $suggestions = ["Open My Loans","Apply loan","Notifications","Calculate loan 50000 12 15%"];
    }
  }

  elseif ($intent === 'calc') {
    preg_match_all('/\d+(\.\d+)?/', $lc, $nums);
    $nums = $nums[0];

    if (count($nums) >= 3) {
      $amount = (float)$nums[0];
      $months = (int)$nums[1];
      $rate   = (float)$nums[2];

      if ($amount <= 0 || $months <= 0 || $rate <= 0) {
        $reply = "Please use positive numbers. Example: calculate loan 50000 12 15%";
      } else {
        $r = ($rate/100) / 12;
        $pmt = ($amount * $r) / (1 - pow(1+$r, -$months));
        $total = $pmt * $months;
        $interest = $total - $amount;

        $reply =
          "Loan estimate (amortized):\n" .
          "- Amount: " . money($amount) . "\n" .
          "- Term: $months months\n" .
          "- Rate: $rate%/year\n\n" .
          "≈ Monthly: " . money($pmt) . "\n" .
          "≈ Total: " . money($total) . "\n" .
          "≈ Interest: " . money($interest);
      }
    } else {
      $reply = "Send: calculate loan AMOUNT TERM_MONTHS RATE%\nExample: calculate loan 50000 12 15%";
    }

    $suggestions = ["Calculate loan 100000 24 18%","Open Apply Loan","Open My Loans","Notifications"];
  }
}
else if ($role === 'admin') {

  if ($intent === 'admin_kyc' || $intent === 'kyc') {
    $stmt = $conn->prepare("SELECT COUNT(*) c FROM kyc_documents WHERE status='pending'");
    $stmt->execute();
    $pending = (int)$stmt->get_result()->fetch_assoc()['c'];

    $reply = "You have $pending pending KYC document(s).\nOpen review page: [[OPEN_ADMIN_KYC]]";
    $suggestions = ["Open Admin KYC","How to approve KYC?","Loan reports","Disbursement"];
  }
  else {
    $reply = "Try:\n- KYC review: [[OPEN_ADMIN_KYC]]\n- Loans: [[OPEN_ADMIN_LOANS]]\n- Dashboard: [[OPEN_ADMIN_DASH]]";
    $suggestions = ["Open Admin KYC","Open Admin Loans","Open Admin Dashboard","Disbursement"];
  }
}

/* ✅ convert [[OPEN_...]] tokens to [LINK:label|url] */
$reply = addLinks($reply, $role);

/* ✅ Save BOT message (unread until user opens widget) */
saveChat($conn, $user_id, "bot", $reply, 0);

echo json_encode([
  "success"=>true,
  "reply"=>$reply,
  "suggestions"=>$suggestions
]);