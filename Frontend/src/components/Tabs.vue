<template>
  <fragment>
    <div class="panel-tabs">
      <div class="tabs">
        <div v-for="{ id, title } in items" class="tab" :key="`tab-${id}`"
             :class="{ active: id === value }" @click="switchTab(id)">
          {{ title }}
        </div>
      </div>
    </div>
    <div v-if="withContent" class="panel panel-content position-relative tabs-content">
      <slot v-if="value != null" :name="`tab(${value})`"/>
    </div>
  </fragment>
</template>

<script>
  export default {
    name: 'Tabs',
    props: {
      items: {
        required: true,
        validator: (v) => v == null || Array.isArray(v),
      },
      value: {
        required: true,
      },
      withContent: {
        type: Boolean,
        default: false,
      },
    },
    created() {
      if (this.value == null && this.items != null && this.items[0] != null) {
        this.$emit('input', this.items[0].id);
      }
    },
    methods: {
      switchTab(id) {
        if (this.value !== id) {
          this.$emit('input', id);
          this.$emit('switch', id);
        }
      },
    },
  };
</script>
