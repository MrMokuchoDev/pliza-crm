@props(['entityType', 'fields' => [], 'groupLabel' => null, 'wireModelPrefix' => 'customFieldValues'])

@php
    use App\Application\CustomField\Services\CustomFieldService;
    use App\Infrastructure\Persistence\Eloquent\LeadModel;
    use App\Infrastructure\Persistence\Eloquent\DealModel;

    $service = app(CustomFieldService::class);

    // Si no se pasaron campos, obtener todos los activos para esta entidad
    if (empty($fields)) {
        $groups = $service->getGroupsByEntity($entityType);
    } else {
        // Si se pasaron campos específicos, agruparlos
        $groups = collect($fields)->groupBy('group_id')->map(function($groupFields) use ($service) {
            $firstField = $groupFields->first();
            $group = $service->getGroupById($firstField->group_id);
            return (object)[
                'id' => $group->id,
                'name' => $group->name,
                'order' => $group->order,
                'fields' => $groupFields
            ];
        })->sortBy('order')->values();
    }

@endphp

@if($groups && count($groups) > 0)
    @foreach($groups as $group)
        @php
            $groupFields = empty($fields)
                ? $service->getFieldsByEntity($entityType, activeOnly: true, groupId: $group->id)
                : $group->fields;
        @endphp

        @if(count($groupFields) > 0)
            {{-- Group Header --}}
            <div class="col-span-full">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide border-b border-gray-200 dark:border-gray-700 pb-2">
                    {{ $group->name }}
                </h4>
            </div>

            {{-- Render Fields --}}
            @foreach($groupFields as $field)
                @php
                    // Cargar opciones dinámicas para campos select/radio/multiselect
                    $fieldOptions = [];
                    if (in_array($field->type, ['select', 'radio', 'multiselect'])) {
                        $optionsManager = app(\App\Domain\CustomField\Services\CustomFieldOptionsTableManagerInterface::class);
                        $fieldName = \App\Domain\CustomField\ValueObjects\FieldName::fromString($field->name);
                        if ($optionsManager->tableExists($fieldName)) {
                            $fieldOptions = $optionsManager->getOptions($fieldName);
                        }
                    }
                @endphp
                <div class="{{ $field->type === 'textarea' ? 'col-span-full' : '' }}">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ $field->label }}
                        @if($field->isRequired)
                            <span class="text-red-500">*</span>
                        @endif
                    </label>

                    @switch($field->type)
                        @case('text')
                        @case('email')
                        @case('url')
                        @case('tel')
                            <input type="{{ $field->type }}"
                                   wire:model="{{ $wireModelPrefix }}.{{ $field->name }}"
                                   @if($field->isRequired) required @endif
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
                                   placeholder="{{ $field->placeholder ?? $field->label }}">
                            @break

                        @case('number')
                            @php
                                // validationRules ya viene como array desde el DTO
                                $rules = is_array($field->validationRules) ? $field->validationRules : [];
                                $min = $rules['min'] ?? null;
                                $max = $rules['max'] ?? null;
                                $decimals = $rules['decimals'] ?? 0;
                                $step = $decimals > 0 ? pow(0.1, $decimals) : 1;
                            @endphp
                            <input type="number"
                                   wire:model="{{ $wireModelPrefix }}.{{ $field->name }}"
                                   step="{{ $step }}"
                                   @if($min !== null) min="{{ $min }}" @endif
                                   @if($max !== null) max="{{ $max }}" @endif
                                   @if($field->isRequired) required @endif
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
                                   placeholder="{{ $field->placeholder ?? $field->label }}">
                            @break

                        @case('textarea')
                            <textarea wire:model="{{ $wireModelPrefix }}.{{ $field->name }}"
                                      rows="3"
                                      @if($field->isRequired) required @endif
                                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
                                      placeholder="{{ $field->placeholder ?? $field->label }}"></textarea>
                            @break

                        @case('date')
                            <input type="date"
                                   wire:model="{{ $wireModelPrefix }}.{{ $field->name }}"
                                   @if($field->isRequired) required @endif
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            @break

                        @case('select')
                            <select wire:model="{{ $wireModelPrefix }}.{{ $field->name }}"
                                    @if($field->isRequired) required @endif
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">Seleccionar...</option>
                                @foreach($fieldOptions as $option)
                                    <option value="{{ $option['value'] }}" @if($field->defaultValue === $option['value']) selected @endif>{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                            @break

                        @case('radio')
                            <div class="space-y-2">
                                @foreach($fieldOptions as $option)
                                    <label class="inline-flex items-center mr-4">
                                        <input type="radio"
                                               wire:model="{{ $wireModelPrefix }}.{{ $field->name }}"
                                               value="{{ $option['value'] }}"
                                               @if($field->isRequired) required @endif
                                               @if($field->defaultValue === $option['value']) checked @endif
                                               class="form-radio text-blue-600 dark:text-blue-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $option['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @break

                        @case('checkbox')
                            <label class="inline-flex items-center">
                                <input type="checkbox"
                                       wire:model.boolean="{{ $wireModelPrefix }}.{{ $field->name }}"
                                       @if($field->isRequired) required @endif
                                       class="form-checkbox text-blue-600 dark:text-blue-500 rounded">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $field->placeholder ?? 'Sí' }}</span>
                            </label>
                            @break

                        @case('multiselect')
                            <select wire:model="{{ $wireModelPrefix }}.{{ $field->name }}"
                                    multiple
                                    @if($field->isRequired) required @endif
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                    size="3">
                                @foreach($fieldOptions as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Mantén presionado Ctrl/Cmd para seleccionar múltiples opciones</p>
                            @break
                    @endswitch

                    @error($field->name)
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            @endforeach
        @endif
    @endforeach
@endif
