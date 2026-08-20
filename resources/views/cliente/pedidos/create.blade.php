@extends('layouts.cliente')
@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-2 text-sm text-stone-500">
        <a href="{{ route('cliente.carrito.index') }}" class="hover:text-egg-700">Carrito</a>
        <i class="fas fa-chevron-right text-[10px]"></i>
        <span class="text-stone-700 font-medium">Confirmar Pedido</span>
    </div>
    <h1 class="text-3xl font-extrabold font-heading text-egg-900 tracking-tight">Confirmar Pedido</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Formulario --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Datos de entrega --}}
            <div class="bg-white rounded-2xl border border-egg-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-200 bg-stone-50/60 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-egg-100 text-egg-700 flex items-center justify-center">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-stone-800">Datos de Entrega</h3>
                        <p class="text-xs text-stone-500">¿Dónde entregamos tus huevos?</p>
                    </div>
                </div>

                <form id="formPedido" method="POST" action="{{ route('cliente.pedidos.store') }}" class="p-6 space-y-5">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-stone-700 mb-2">Dirección de entrega *</label>
                            <input type="text" name="direccion_entrega" value="{{ old('direccion_entrega') }}" required maxlength="255"
                                   class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none transition-all"
                                   placeholder="Calle, carrera, número...">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-stone-700 mb-2">Barrio *</label>
                            <input type="text" name="barrio" value="{{ old('barrio') }}" required maxlength="100"
                                   class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none transition-all"
                                   placeholder="Nombre del barrio">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-stone-700 mb-2">Teléfono de contacto *</label>
                            <input type="text" name="telefono_entrega" value="{{ old('telefono_entrega') }}" required maxlength="20"
                                   class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none transition-all"
                                   placeholder="300 000 0000">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-stone-700 mb-2">Referencia del lugar</label>
                            <input type="text" name="referencia" value="{{ old('referencia') }}" maxlength="255"
                                   class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none transition-all"
                                   placeholder="Ej: Casa azul con portón negro, frente al parque">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-stone-700 mb-2">Observaciones adicionales</label>
                            <textarea name="observaciones" rows="2"
                                      class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none transition-all"
                                      placeholder="Indicaciones especiales para el domiciliario...">{{ old('observaciones') }}</textarea>
                        </div>
                    </div>

                    <hr class="border-stone-100">

                    {{-- Método de pago --}}
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-stone-400 mb-4 flex items-center gap-2">
                            <i class="fas fa-credit-card text-egg-500"></i> Método de Pago
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <label class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all {{ old('metodo_pago','efectivo')==='efectivo' ? 'border-egg-500 bg-egg-50' : 'border-stone-200 hover:border-egg-300' }}">
                                <input type="radio" name="metodo_pago" value="efectivo" {{ old('metodo_pago','efectivo')==='efectivo'?'checked':'' }} class="text-egg-600">
                                <div>
                                    <p class="font-semibold text-sm text-egg-900">💵 Efectivo</p>
                                    <p class="text-xs text-stone-500">Al recibir</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all {{ old('metodo_pago')==='transferencia' ? 'border-blue-500 bg-blue-50' : 'border-stone-200 hover:border-blue-300' }}">
                                <input type="radio" name="metodo_pago" value="transferencia" {{ old('metodo_pago')==='transferencia'?'checked':'' }} class="text-blue-600">
                                <div>
                                    <p class="font-semibold text-sm text-egg-900">🏦 Transferencia</p>
                                    <p class="text-xs text-stone-500">Bancaria</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all {{ old('metodo_pago')==='nequi' ? 'border-purple-500 bg-purple-50' : 'border-stone-200 hover:border-purple-300' }}">
                                <input type="radio" name="metodo_pago" value="nequi" {{ old('metodo_pago')==='nequi'?'checked':'' }} class="text-purple-600">
                                <div>
                                    <p class="font-semibold text-sm text-egg-900">📱 Nequi</p>
                                    <p class="text-xs text-stone-500">Pago digital</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-stone-700 mb-2">Referencia de pago (opcional)</label>
                        <input type="text" name="referencia_pago" value="{{ old('referencia_pago') }}" maxlength="100"
                               class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none transition-all"
                               placeholder="Número de comprobante o referencia">
                    </div>

                </form>
            </div>
        </div>

        {{-- Resumen pedido --}}
        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-egg-200 shadow-sm p-6 space-y-4">
                <h3 class="font-heading font-bold text-egg-900 text-lg">Resumen</h3>

                <div class="space-y-3">
                    @foreach($carrito->detalles as $d)
                    <div class="flex justify-between items-center text-sm">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="text-stone-400 flex-shrink-0">{{ $d->cantidad }}×</span>
                            <span class="text-stone-700 truncate">{{ $d->producto->nombre }}</span>
                        </div>
                        <span class="font-semibold text-stone-800 flex-shrink-0 ml-2">${{ number_format($d->subtotal,2) }}</span>
                    </div>
                    @endforeach
                </div>

                <div class="border-t border-stone-200 pt-4 flex justify-between items-center">
                    <span class="font-bold text-egg-900 font-heading text-lg">Total</span>
                    <span class="font-extrabold text-egg-800 text-2xl font-heading">${{ number_format($total,2) }}</span>
                </div>

                <button type="submit" form="formPedido"
                        class="w-full bg-gradient-to-r from-egg-900 to-egg-800 hover:from-egg-800 hover:to-egg-700 text-egg-400 font-bold py-4 rounded-xl shadow-lg hover:shadow-xl transition-all active:scale-[0.98] font-heading flex items-center justify-center gap-2">
                    <i class="fas fa-check-circle"></i> Confirmar Pedido
                </button>

                <a href="{{ route('cliente.carrito.index') }}"
                   class="block text-center text-xs text-stone-500 hover:text-egg-700 transition-colors font-medium">
                    ← Volver al carrito
                </a>
            </div>

            <div class="bg-egg-50 border border-egg-200 rounded-2xl p-4 text-xs text-egg-800 space-y-2">
                <p class="font-bold flex items-center gap-1"><i class="fas fa-info-circle text-egg-600"></i> Información</p>
                <p class="leading-relaxed text-egg-700">Al confirmar, el stock se reserva automáticamente y tu pedido quedará en estado <strong>Pendiente</strong>.</p>
            </div>
        </div>
    </div>
</div>
@endsection
