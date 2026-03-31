<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Google Fonts: Inter -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Tailwind CSS via CDN -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest"></script>

<script>
    // Tailwind Config
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                fontFamily: {
                    sans: ['Inter', 'sans-serif'],
                },
                colors: {
                    brand: {
                        50: '#f0fdfa',
                        100: '#ccfbf1',
                        200: '#99f6e4',
                        300: '#5eead4',
                        400: '#2dd4bf',
                        500: '#14b8a6', // Teal main
                        600: '#0d9488',
                        700: '#0f766e',
                        800: '#115e59',
                        900: '#134e4a',
                    }
                }
            }
        }
    }

    // Global Toast Notification System
    function showToast(message, type = 'success') {
        const toastContainer = document.getElementById('toast-container') || createToastContainer();
        
        const toast = document.createElement('div');
        
        const bgColors = {
            success: 'bg-emerald-500',
            error: 'bg-red-500',
            warning: 'bg-amber-500',
            info: 'bg-blue-500'
        };
        
        const icons = {
            success: '<i data-lucide="check-circle" class="w-5 h-5"></i>',
            error: '<i data-lucide="alert-circle" class="w-5 h-5"></i>',
            warning: '<i data-lucide="alert-triangle" class="w-5 h-5"></i>',
            info: '<i data-lucide="info" class="w-5 h-5"></i>'
        };

        const bgColor = bgColors[type] || bgColors['info'];
        const icon = icons[type] || icons['info'];

        toast.className = `transform transition-all duration-300 ease-out translate-y-2 opacity-0 flex items-center p-4 mb-3 text-white rounded-lg shadow-lg ${bgColor} max-w-sm w-full`;
        
        toast.innerHTML = `
            <div class="inline-flex flex-shrink-0 justify-center items-center mr-3">
                ${icon}
            </div>
            <div class="font-medium text-sm">${message}</div>
            <button class="ml-auto -mx-1.5 -my-1.5 rounded-lg p-1 hover:bg-white/20 inline-flex h-8 w-8 transition-colors" onclick="this.parentElement.remove()">
                <i data-lucide="x" class="w-4 h-4 mt-1 ml-1 text-white"></i>
            </button>
        `;

        toastContainer.appendChild(toast);
        lucide.createIcons();

        // Animate in
        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-2', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
        });

        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('opacity-0', '-translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed bottom-5 right-5 z-50 flex flex-col items-end min-w-[300px]';
        document.body.appendChild(container);
        return container;
    }

    // Shared Helper for API calls
    async function apiCall(endpoint, data = null) {
        try {
            const options = {
                method: data ? 'POST' : 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            };
            
            if (data instanceof FormData) {
                options.body = data;
            } else if (data) {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(data);
            }
            
            const res = await fetch(`<?= $basePath ?? '' ?>/backend/api/${endpoint}`, options);
            const contentType = res.headers.get("content-type");
            
            if (contentType && contentType.indexOf("application/json") !== -1) {
                const json = await res.json();
                if (!res.ok) {
                    throw new Error(json.message || "An error occurred");
                }
                return json;
            } else {
                throw new Error("Server returned an invalid response.");
            }
        } catch (error) {
            showToast(error.message, 'error');
            throw error;
        }
    }
</script>

<style>
    /* Base styles over Tailwind */
    body {
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    
    /* Hide scrollbar but keep functionality for clean UI */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
