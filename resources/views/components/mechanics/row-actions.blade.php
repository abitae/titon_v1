@props([
    'edit' => null,
    'delete' => null,
    'deleteConfirm' => '¿Eliminar este registro?',
])

<x-platform.action-buttons
    :edit="$edit"
    :delete="$delete"
    :delete-confirm="$deleteConfirm"
/>
