<template>
    <div 
        v-if="$can('roles.update')"
        class="buttons">
        <a 
            href="#"
            @click.prevent="$emit('click')">
            {{ __('Редактировать группу') }}
        </a>
    </div>
</template>