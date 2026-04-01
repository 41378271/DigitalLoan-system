<?php 
session_start();
require_once "../../backend/config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Check latest KYC status
$stmt = $conn->prepare("
    SELECT status 
    FROM kyc_documents 
    WHERE user_id = ?
    ORDER BY uploaded_at DESC 
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

$kyc_status = $row['status'] ?? 'not_uploaded';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Apply for Loan | KashFlow</title>
    <?php include '../partials/head.php'; ?>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased overflow-hidden">

    <?php include '../partials/navbar.php'; ?>
    <?php include '../partials/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="md:ml-64 pt-16 h-screen overflow-y-auto pb-24 md:pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <?php if ($kyc_status !== 'approved'): ?>
                <!-- KYC Warning State -->
                <div class="flex flex-col items-center justify-center min-h-[60vh] text-center max-w-lg mx-auto">
                    <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mb-6">
                        <i data-lucide="shield-alert" class="w-10 h-10 text-amber-600"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Verification Required</h2>
                    <p class="text-gray-500 mb-8">You must have an approved KYC profile before applying for a loan. This helps us keep the platform secure and comply with financial regulations.</p>
                    <a href="kyc" class="bg-brand-600 text-white rounded-xl px-8 py-3 font-semibold hover:bg-brand-700 transition-colors shadow-sm inline-flex items-center gap-2 border border-transparent">
                        <i data-lucide="upload" class="w-4 h-4"></i> Upload Documents
                    </a>
                    <a href="dashboard" class="mt-4 text-gray-500 hover:text-gray-900 font-medium text-sm transition-colors">Return to Dashboard</a>
                </div>
            <?php else: ?>

                <!-- Application Header -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-900">Apply for a Loan</h1>
                    <p class="text-sm text-gray-500 mt-1">Fill in the details below. Our live calculator will show your expected repayments immediately.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                    
                    <!-- Left Column: Form -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <form id="loanForm" class="p-6 sm:p-8" enctype="multipart/form-data">
                                
                                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-full bg-brand-50 flex items-center justify-center text-brand-600 text-sm"><i data-lucide="banknote" class="w-4 h-4"></i></span>
                                    Loan Details
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                    <!-- Amount -->
                                    <div>
                                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Loan Amount (KES)</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <span class="text-gray-500 font-medium">KES</span>
                                            </div>
                                            <input type="number" id="amount" name="amount" required min="1000" step="100" class="block w-full pl-14 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors sm:text-sm font-mono outline-none" placeholder="10,000">
                                        </div>
                                    </div>

                                    <!-- Term -->
                                    <div>
                                        <label for="term_months" class="block text-sm font-medium text-gray-700 mb-1">Duration (Months)</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <i data-lucide="calendar" class="w-5 h-5 text-gray-400"></i>
                                            </div>
                                            <input type="number" id="term_months" name="term_months" required min="1" max="36" step="1" class="block w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors sm:text-sm font-mono outline-none" placeholder="3">
                                        </div>
                                    </div>
                                </div>

                                <hr class="border-gray-100 mb-8 w-full">

                                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-600 text-sm"><i data-lucide="lock" class="w-4 h-4"></i></span>
                                    Collateral / Security
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <!-- Collateral Type -->
                                    <div>
                                        <label for="collateral_type" class="block text-sm font-medium text-gray-700 mb-1">Collateral Type</label>
                                        <select id="collateral_type" name="collateral_type" required class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors sm:text-sm outline-none appearance-none">
                                            <option value="" disabled selected>Select an option</option>
                                            <option value="phone">Smartphone</option>
                                            <option value="motorbike">Motorbike (Logbook)</option>
                                            <option value="laptop">Laptop / Electronics</option>
                                            <option value="land_title">Land Title Deed</option>
                                            <option value="guarantor">Personal Guarantor</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>

                                    <!-- Collateral Value -->
                                    <div>
                                        <label for="collateral_value" class="block text-sm font-medium text-gray-700 mb-1">Estimated Value (KES)</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <span class="text-gray-500 font-medium">KES</span>
                                            </div>
                                            <input type="number" id="collateral_value" name="collateral_value" required min="1" step="0.01" class="block w-full pl-14 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors sm:text-sm font-mono outline-none" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mb-6">
                                    <label for="collateral_description" class="block text-sm font-medium text-gray-700 mb-1">Asset Description</label>
                                    <textarea id="collateral_description" name="collateral_description" required rows="2" class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors sm:text-sm outline-none resize-none" placeholder="E.g., Samsung Galaxy S23 Ultra, IMEI Number..."></textarea>
                                </div>

                                <!-- File Upload -->
                                <div class="mb-8">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload Proof (Optional)</label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-200 border-dashed rounded-xl transition-colors hover:border-brand-400 bg-gray-50 relative group">
                                        <div class="space-y-1 text-center">
                                            <i data-lucide="image" class="mx-auto h-12 w-12 text-gray-400 group-hover:text-brand-500 transition-colors"></i>
                                            <div class="flex text-sm text-gray-600 justify-center">
                                                <label for="collateral_proof" class="relative cursor-pointer rounded-md bg-transparent font-medium text-brand-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-brand-500 focus-within:ring-offset-2 hover:text-brand-500">
                                                    <span>Upload a file</span>
                                                    <input id="collateral_proof" name="collateral_proof" type="file" class="sr-only" accept=".pdf,.jpg,.jpeg,.png">
                                                </label>
                                                <p class="pl-1">or drag and drop</p>
                                            </div>
                                            <p class="text-xs text-gray-500" id="fileNameDisplay">PNG, JPG, PDF up to 5MB</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Section -->
                                <div class="bg-brand-50 -mx-6 sm:-mx-8 -mb-6 sm:-mb-8 px-6 sm:px-8 py-6 border-t border-brand-100 flex items-center justify-between">
                                    <p class="text-xs text-brand-600 font-medium max-w-xs">By applying, you agree to our flat 3% monthly interest rate policy.</p>
                                    <button type="submit" id="submitBtn" class="bg-brand-600 text-white rounded-xl px-8 py-3 font-semibold hover:bg-brand-700 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                        Submit Application <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>

                    <!-- Right Column: Live Calculator -->
                    <div class="lg:col-span-1">
                        <div class="bg-gray-900 rounded-2xl shadow-xl overflow-hidden sticky top-24">
                            <div class="p-6">
                                <h3 class="text-white font-bold text-lg mb-6 flex items-center justify-between">
                                    Summary
                                    <i data-lucide="calculator" class="w-5 h-5 text-brand-400"></i>
                                </h3>

                                <div class="space-y-6">
                                    <!-- Dynamic Values -->
                                    <div class="flex justify-between items-end border-b border-gray-800 pb-4">
                                        <p class="text-sm font-medium text-gray-400">Principal</p>
                                        <p class="text-lg font-bold text-white font-mono" id="calcPrincipal">KES 0.00</p>
                                    </div>
                                    <div class="flex justify-between items-end border-b border-gray-800 pb-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-400">Total Interest</p>
                                            <p class="text-xs text-gray-500 mt-1">Flat 3% per month</p>
                                        </div>
                                        <p class="text-lg font-bold text-red-400 font-mono" id="calcInterest">KES 0.00</p>
                                    </div>
                                    <div class="flex justify-between items-end border-b border-gray-800 pb-4">
                                        <p class="text-sm font-medium text-gray-400">Total Payable</p>
                                        <p class="text-lg font-bold text-white font-mono" id="calcTotal">KES 0.00</p>
                                    </div>
                                </div>

                                <!-- EMI Highlight block -->
                                <div class="mt-8 bg-brand-600/20 border border-brand-500/30 rounded-xl p-6 relative overflow-hidden">
                                    <div class="absolute right-0 top-0 w-24 h-24 bg-brand-500 opacity-20 rounded-bl-full -mr-4 -mt-4 blur-xl"></div>
                                    <p class="text-sm font-medium text-brand-200 mb-1 relative z-10">Monthly Payment (EMI)</p>
                                    <p class="text-3xl font-bold text-white tracking-tight font-mono relative z-10" id="calcEmi">KES 0.00</p>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>

            <?php endif; ?>

        </div>
    </main>

    <!-- Scripts -->
    <script>
        lucide.createIcons();

        <?php if ($kyc_status === 'approved'): ?>
        
        // --- Live Calculator Logic ---
        const amtInput = document.getElementById('amount');
        const termInput = document.getElementById('term_months');
        
        const calcPrincipal = document.getElementById('calcPrincipal');
        const calcInterest = document.getElementById('calcInterest');
        const calcTotal = document.getElementById('calcTotal');
        const calcEmi = document.getElementById('calcEmi');

        const INTEREST_RATE = 0.03; // 3% flat monthly

        const fmtMoney = (n) => Number(n || 0).toLocaleString('en-KE', {minimumFractionDigits:2, maximumFractionDigits:2});

        function updateCalculator() {
            const principal = parseFloat(amtInput.value) || 0;
            const months = parseInt(termInput.value) || 0;

            if (principal > 0 && months > 0) {
                // Flat interest = P * r * t
                const interest = principal * INTEREST_RATE * months;
                const total = principal + interest;
                const emi = total / months;

                calcPrincipal.innerText = `KES ${fmtMoney(principal)}`;
                calcInterest.innerText = `KES ${fmtMoney(interest)}`;
                calcTotal.innerText = `KES ${fmtMoney(total)}`;
                calcEmi.innerText = `KES ${fmtMoney(emi)}`;
            } else {
                calcPrincipal.innerText = `KES 0.00`;
                calcInterest.innerText = `KES 0.00`;
                calcTotal.innerText = `KES 0.00`;
                calcEmi.innerText = `KES 0.00`;
            }
        }

        amtInput.addEventListener('input', updateCalculator);
        termInput.addEventListener('input', updateCalculator);

        // --- File Upload UI ---
        const fileInput = document.getElementById('collateral_proof');
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                fileNameDisplay.innerHTML = `<span class="text-brand-600 font-medium">Selected:</span> ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
            } else {
                fileNameDisplay.innerText = "PNG, JPG, PDF up to 5MB";
            }
        });

        // --- Form Submission ---
        const form = document.getElementById('loanForm');
        const submitBtn = document.getElementById('submitBtn');

        form.addEventListener("submit", async function(e) {
            e.preventDefault();
            
            // Validate flat constraints matching calc state
            const principal = parseFloat(amtInput.value) || 0;
            const months = parseInt(termInput.value) || 0;
            if (principal <= 0 || months <= 0) {
                showToast("Please enter a valid amount and term.", "error");
                return;
            }

            // Disable button
            submitBtn.disabled = true;
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = `<i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i> Submitting...`;
            lucide.createIcons();

            try {
                const formData = new FormData(this);
                
                // Using the unified apiCall wrapper from partials/navbar.php if available, 
                // else raw fetch. We'll use raw fetch with our toast handler to be safe.
                const res = await fetch("<?= $basePath ?? '' ?>/backend/api/loans/apply.php", {
                    method: "POST",
                    body: formData
                });
                const data = await res.json();
                
                if (data.success) {
                    showToast(data.message, "success");
                    form.reset();
                    updateCalculator(); // Reset calc
                    fileNameDisplay.innerText = "PNG, JPG, PDF up to 5MB"; // Reset file text
                    
                    // Redirect to loans list after a short delay
                    setTimeout(() => {
                        window.location.href = '/my-loans';
                    }, 2000);
                } else {
                    showToast(data.message || "Failed to submit application.", "error");
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    lucide.createIcons();
                }
            } catch (err) {
                console.error("Apply loan error: ", err);
                showToast("A network error occurred.", "error");
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                lucide.createIcons();
            }
        });

        <?php endif; ?>
    </script>
</body>
</html>