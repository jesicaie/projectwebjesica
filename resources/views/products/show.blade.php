<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Product</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Nama</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $product->name }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Kategori</p>
                            <p class="text-gray-900">{{ $product->category ?? '-' }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Deskripsi</p>
                            <p class="text-gray-900">{{ $product->description ?? '-' }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Harga</p>
                                <p class="text-gray-900">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Stock</p>
                                <p class="text-gray-900">{{ $product->stock }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Status</p>
                                <p class="text-gray-900">{{ $product->is_active ? 'Aktif' : 'Tidak Aktif' }}</p>
                            </div>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Dibuat</p>
                            <p class="text-gray-900">{{ $product->created_at->translatedFormat('d F Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-between">
                        <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Kembali
                        </a>
                        <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Edit
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
