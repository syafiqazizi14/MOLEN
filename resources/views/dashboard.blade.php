<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    @include('viteall')
    <link rel="icon" href="/Logo BPS.png" type="image/png">
    <title>Dashboard 3516</title>
</head>

<body>
    <!-- SweetAlert Logic -->
    @if (session('success'))
        <script>
            swal("Success!", "{{ session('success') }}", "success");
        </script>
    @endif

    @if ($errors->any())
        <script>
            swal("Error!", "{{ $errors->first() }}", "error");
        </script>
    @endif

    <!-- Birthday Popup Modal -->
    <div id="birthdayModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
        <!-- Backdrop dengan blur -->
        <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm" onclick="closeBirthdayModal()"></div>
        
        <div class="relative bg-white rounded-3xl shadow-2xl max-w-md w-full mx-4 overflow-hidden animate-slide-up">
            <!-- Header with premium gradient -->
            <div class="relative bg-gradient-to-br from-rose-400 via-pink-400 to-orange-400 p-8 text-center overflow-hidden">
                <!-- Animated background elements -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-white opacity-10 rounded-full -ml-12 -mb-12"></div>
                
                <div class="relative z-10">
                    <div class="text-7xl mb-4 animate-bounce-slow">🎂</div>
                    <h2 class="text-4xl font-bold text-white mb-2">Selamat Ulang Tahun!</h2>
                    <p class="text-white text-opacity-95 text-lg font-semibold">Mari rayakan hari istimewa ini 🎉</p>
                </div>
            </div>
            
            <!-- Body -->
            <div class="p-6 max-h-80 overflow-y-auto" id="birthdayList">
                <!-- Will be populated by JavaScript -->
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-5 text-center border-t-2">
                <button onclick="closeBirthdayModal()" 
                        class="bg-gradient-to-r from-rose-500 via-pink-500 to-orange-500 hover:from-rose-600 hover:via-pink-600 hover:to-orange-600 text-white font-bold px-8 py-3 rounded-full shadow-lg transition-all duration-300 transform hover:scale-110 active:scale-95">
                    <i class="fas fa-check mr-2"></i> Tutup
                </button>
            </div>
        </div>
    </div>
    
    <section class="bg-[url('/public/dashboard2.png')] bg-cover bg-center min-h-screen">
<link rel="icon" href="{{ asset('favicon.ico') }}">
        <header>

            <nav class=" px-4 lg:px-6 py-2">
                <div class="grid lg:grid-cols-2">
                    <div class="flex flex-wrap justify-between items-center mx-auto max-w-screen-xl">
                        <div class="flex items-center whitespace-nowrap shadow-lg p-2">
                            <img src="logo aja bps.png" class="mr-3 h-11 sm:h-17" alt="Logo BPS" />
                            <span class="self-center text-xl font-semibold text-white">
                                BPS Kabupaten Mojokerto
                            </span>

                        </div>
                    </div>
                    <div class="flex flex-wrap justify-between items-center mx-auto max-w-screen-xl mt-4">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-secondary-button :href="route('logout')"
                                onclick="event.preventDefault();
                                    this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-secondary-button>
                        </form>
                    </div>
                </div>

            </nav>
        </header>
        <div class="relative isolate px-6 pt-14 lg:px-8">
            <div class="mx-auto max-w-6xl py-16 sm:py-24 lg:py-6">
                <div class="text-center items-center justify-center">
                <img class="mx-auto" style="max-width: 300px; min-width: 80px; height: auto; width: 20vw;" src="kanal3516.png" alt="logo">
                  
                <div class="flex justify-center items-center">
                    <span class="nowrap text-4xl font-bold tracking-tight sm:text-6xl text-white py-3  text-center">
                        KUMPULAN APLIKASI INTERNAL 3516
                    </span>
                </div>

                    <section class="dark:bg-gray-900">
                        <div class="py-8 px-4 mx-auto max-w-screen-xl lg:py-16 lg:px-6">
                            <div class="grid gap-8 lg:grid-cols-2">
                                
                                <article
                                    class="p-6 bg-white bg-opacity-50 rounded-lg border border-gray-200 shadow-md transition-all duration-300 flex flex-col items-center justify-center gap-3 hover:bg-opacity-90 hover:bg-gray-300">
                                    <a href="/agenkitapresensi">
                                        <img src="agen kita.png" height="200" width="175" class=""
                                            alt="Logo agenkita" />
                                        <h2
                                            class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                            AGEN KITA</h2>
                                    </a>
                                </article>    
                                

                                @if (auth()->user()->is_admin || auth()->user()->is_hamukti)
                                    <article
                                        class="p-6 bg-white bg-opacity-50 rounded-lg border border-gray-200 shadow-md transition-all duration-300 flex flex-col items-center justify-center gap-3 hover:bg-opacity-90 hover:bg-gray-300">
                                        <a href="/hamuktisuratkeluar">
                                            <img src="hamukti.png" height="300" width="175" alt="Logo hamukti" />
                                            <h2
                                                class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                                HAMUKTI</h2>
                                        </a>
                                    </article>
                                @endif

                                @if (auth()->user()->is_admin || auth()->user()->is_leader)
                                    <article
                                        class="p-6 bg-white bg-opacity-50 rounded-lg border border-gray-200 shadow-md transition-all duration-300 flex flex-col items-center justify-center gap-3 hover:bg-opacity-90 hover:bg-gray-300">
                                        <a href="/izinkeluar">
                                            <img src="prisma.png" height="300" width="175" alt="Logo hamukti" />
                                            <h2
                                                class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                                PRISMA</h2>
                                        </a>
                                    </article>
                                @else
                                    <article
                                        class="p-6 bg-white bg-opacity-50 rounded-lg border border-gray-200 shadow-md transition-all duration-300 flex flex-col items-center justify-center gap-3 hover:bg-opacity-90 hover:bg-gray-300">
                                        <a href="/izinkeluarform">
                                            <img src="prisma.png" height="300" width="175" alt="Logo hamukti" />
                                            <h2
                                                class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                                PRISMA</h2>
                                        </a>
                                    </article>
                                @endif
                                
                                {{-- <article
                                    class="p-6 bg-white bg-opacity-50 rounded-lg border border-gray-200 shadow-md transition-all duration-300 flex flex-col items-center justify-center gap-3 hover:bg-opacity-90 hover:bg-gray-300">
                                    <a href="https://www.link3516.com" target="_blank">
                                        <img src="supertim.png" height="200" width="175"
                                            alt="Logo BPS Kabupaten Mojokerto" />
                                        <h2
                                            class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                            SETAPE</h2>
                                    </a>
                                </article> --}}
                                
                               <article
  class="p-6 bg-white bg-opacity-50 rounded-lg border border-gray-200 shadow-md transition-all duration-300 flex flex-col items-center justify-center gap-3 hover:bg-opacity-90 hover:bg-gray-300">
  <a href="{{ (auth()->user()->jabatan === 'admin' || auth()->user()->jabatan === 'Kasubag Umum')
                ? url('/siminbarpermintaanbarangadmin')
                : url('/siminbarpermintaanbarang') }}">
    <img src="siminbar.png" height="300" width="175" alt="Logo siminbar" />
    <h2 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">SIMINBAR</h2>
  </a>
</article>

                                <article
                                    class="p-6 bg-white bg-opacity-50 rounded-lg border border-gray-200 shadow-md transition-all duration-300 flex flex-col items-center justify-center gap-3 hover:bg-opacity-90 hover:bg-gray-300">
                                    <a href="{{ route('mitra.recommendation.index') }}">
                                        <img src="mitrabps.png" height="300" width="175" alt="Logo siminbar" />
                                        <h2
                                            class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                            </h2>
                                    </a>
                                </article>

                                <article
                                    class="p-6 bg-white bg-opacity-50 rounded-lg border border-gray-200 shadow-md transition-all duration-300 flex flex-col items-center justify-center gap-3 hover:bg-opacity-90 hover:bg-gray-300">
                                    <a href="/supertim">
                                        <img src="supertim.png" height="200" width="175"
                                            alt="Logo BPS Kabupaten Mojokerto" />
                                        <h2
                                            class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                            SETAPE</h2>
                                    </a>
                                </article>
                                
                                <article
                                    class="p-6 bg-white bg-opacity-50 rounded-lg border border-gray-200 shadow-md transition-all duration-300 flex flex-col items-center justify-center gap-3 hover:bg-opacity-90 hover:bg-gray-300">
                                    <a href="https://sites.google.com/view/disiplin3516/home">
                                        <img src="kupetik.png" height="200" width="175"
                                            alt="Logo BPS Kabupaten Mojokerto" />
                                        <h2
                                            class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                            KUPETIK</h2>
                                    </a>
                                </article>
            
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>


    </section>

    <!-- Birthday Modal Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const today = '{{ date("Y-m-d") }}';
            const lastShown = localStorage.getItem('birthday_shown_date');
            const birthdays = @json($birthdays ?? []);
            
            // Show popup only once per day if there are birthdays
            if (lastShown !== today && birthdays.length > 0) {
                let html = '';
                birthdays.forEach((user, index) => {
                    const imagePath = user.gambar 
                        ? `/storage/uploads/images/${user.gambar}` 
                        : '/person.png';
                    
                    const colors = ['from-rose-50', 'from-amber-50', 'from-emerald-50', 'from-cyan-50'];
                    const borderColors = ['border-rose-300', 'border-amber-300', 'border-emerald-300', 'border-cyan-300'];
                    const textColors = ['text-rose-600', 'text-amber-600', 'text-emerald-600', 'text-cyan-600'];
                    
                    html += `
                        <div class="flex items-center gap-4 mb-4 p-4 bg-gradient-to-r ${colors[index % colors.length]} to-white rounded-2xl border-l-4 ${borderColors[index % borderColors.length]} hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                            <div class="relative">
                                <img src="${imagePath}" 
                                     alt="${user.name}"
                                     class="w-16 h-16 rounded-full object-cover border-4 ${borderColors[index % borderColors.length]} shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <div class="absolute -bottom-1 -right-1 bg-yellow-400 rounded-full p-1.5 animate-pulse">
                                    <i class="fas fa-star text-yellow-600 text-xs"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="font-bold text-lg text-gray-800 ${textColors[index % textColors.length]}">${user.name}</p>
                                <p class="text-sm text-gray-600 flex items-center gap-2 mt-1">
                                    <i class="fas fa-gift text-pink-500 animate-bounce"></i>
                                    <span class="font-semibold">Semoga panjang umur & sukses! 🎈</span>
                                </p>
                            </div>
                        </div>
                    `;
                });
                
                document.getElementById('birthdayList').innerHTML = html;
                document.getElementById('birthdayModal').classList.remove('hidden');
            }
        });

        function closeBirthdayModal() {
            localStorage.setItem('birthday_shown_date', '{{ date("Y-m-d") }}');
            document.getElementById('birthdayModal').classList.add('hidden');
        }

        // Optional: Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeBirthdayModal();
            }
        });
    </script>

    <style>
        @keyframes slide-up {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        @keyframes bounce-slow {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        .animate-slide-up {
            animation: slide-up 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        
        .animate-bounce-slow {
            animation: bounce-slow 3s infinite;
        }
    </style>

</body>

</html>
