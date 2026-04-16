<!-- STICKY HEADER -->
<nav class="sticky top-0 z-50 w-full bg-white shadow-sm flex items-center justify-between border-b-[2px] border-[#D31215] px-5 py-3" x-cloak>
    <!-- Left: Hồ Sơ 1-2-1 -->
    <div class="flex-1 flex justify-start items-center whitespace-nowrap">
        <span class="text-[18px] sm:text-[22px] font-philosopher font-bold text-[#D31215] tracking-wide mt-1">
            Hồ Sơ 1-2-1
        </span>
    </div>
    
    <!-- Middle: BNI Logo -->
    <div class="flex-none flex justify-center items-center">
        <img src="{{ asset('images/bnilogo.jpg') }}" alt="BNI Logo" class="w-[90px] md:w-[100px] aspect-[4/3] object-cover object-center mix-blend-multiply">
    </div>
    
    <!-- Right: Hamburger -->
    <div class="flex-1 flex justify-end items-center">
        <button @click="drawerOpen = true" class="flex flex-col justify-center items-end gap-[6px] focus:outline-none">
            <span class="w-[34px] h-[3.5px] bg-[#D31215] rounded-[1px]"></span>
            <span class="w-[34px] h-[3.5px] bg-[#D31215] rounded-[1px]"></span>
            <span class="w-[34px] h-[3.5px] bg-[#D31215] rounded-[1px]"></span>
        </button>
    </div>
</nav>

<!-- DRAWER -->
<div
    x-show="drawerOpen"
    x-cloak
    style="display: none;"
    class="fixed inset-0 z-[100]"
    aria-labelledby="slide-over-title"
    role="dialog"
    aria-modal="true"
    @keydown.escape.window="drawerOpen = false"
>
    <div
        x-show="drawerOpen"
        x-transition:enter="ease-in-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in-out duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute inset-0 bg-black/60 backdrop-blur-sm"
        @click="drawerOpen = false"
    ></div>

    <div class="absolute inset-y-0 right-0 flex w-full max-w-[380px]">
        <div
            x-show="drawerOpen"
            x-transition:enter="transform transition ease-in-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in-out duration-300"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="pointer-events-auto h-full w-full bg-white shadow-2xl border-l border-gray-100 flex flex-col"
        >
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900" id="slide-over-title">MENU</h2>
                <button type="button" @click="drawerOpen = false" class="rounded-full bg-gray-50 p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-none transition-colors">
                    <i class="bi bi-x-lg text-lg leading-none"></i>
                </button>
            </div>

            <div class="relative flex-1 px-5 py-6 overflow-y-auto bg-white">
                <nav class="flex flex-col gap-2">
                    <a href="#top" @click="drawerOpen = false" class="flex items-center gap-3 px-3 py-3 rounded-xl text-[15px] font-semibold text-gray-700 hover:text-white hover:bg-[var(--primary-color)] transition-all">
                        <i class="bi bi-house-door text-lg"></i> Đầu trang
                    </a>
                    <a href="#thong-tin-ca-nhan" @click="drawerOpen = false" class="flex items-center gap-3 px-3 py-3 rounded-xl text-[15px] font-semibold text-gray-700 hover:text-white hover:bg-[var(--primary-color)] transition-all">
                        <i class="bi bi-person text-lg"></i> Thông tin cá nhân
                    </a>
                    <a href="#album-ca-nhan" @click="drawerOpen = false" class="flex items-center gap-3 px-3 py-3 rounded-xl text-[15px] font-semibold text-gray-700 hover:text-white hover:bg-[var(--primary-color)] transition-all">
                        <i class="bi bi-images text-lg"></i> Album cá nhân
                    </a>
                    <a href="#thong-tin-doanh-nghiep" @click="drawerOpen = false" class="flex items-center gap-3 px-3 py-3 rounded-xl text-[15px] font-semibold text-gray-700 hover:text-white hover:bg-[var(--primary-color)] transition-all">
                        <i class="bi bi-briefcase text-lg"></i> Doanh nghiệp
                    </a>
                    <a href="#san-pham" @click="drawerOpen = false" class="flex items-center gap-3 px-3 py-3 rounded-xl text-[15px] font-semibold text-gray-700 hover:text-white hover:bg-[var(--primary-color)] transition-all">
                        <i class="bi bi-box-seam text-lg"></i> Sản phẩm
                    </a>
                    <a href="#bang-gains" @click="drawerOpen = false" class="flex items-center gap-3 px-3 py-3 rounded-xl text-[15px] font-semibold text-gray-700 hover:text-white hover:bg-[var(--primary-color)] transition-all">
                        <i class="bi bi-table text-lg"></i> Bảng GAINS
                    </a>
                    <a href="#referral" @click="drawerOpen = false" class="flex items-center gap-3 px-3 py-3 rounded-xl text-[15px] font-semibold text-gray-700 hover:text-white hover:bg-[var(--primary-color)] transition-all">
                        <i class="bi bi-people text-lg"></i> Referral
                    </a>
                </nav>
            </div>
        </div>
    </div>
</div>
