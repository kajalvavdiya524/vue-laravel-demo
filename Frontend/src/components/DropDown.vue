<template>
  <div class="form-control d-flex align-items-center justify-content-between drop-down-container drop-down"
       :id="$uid"
       :disabled="disabled"
       :tabindex="disabled ? -1 : 0"
       @focus="focused"
       @blur="blurred"
       @mousedown="lastEvent = 'm'"
       @keydown="lastEvent = 'k'"
       @click="clicked"
  >
    <span>{{ ddTitle }}</span>
    <icon class="arrow" stroke-width="2" width="12" height="7" type="arrow-down"/>
    <ul ref="menu" class="drop-down-menu position-absolute d-none" :class="menuClass">
      <template v-if="items != null">
        <template v-if="multiple && selectAll">
          <li>
            <check-box @input="toggleAllItems($event)"
                       :value="isSelectedAll">{{ $t(titleAll) }}
            </check-box>
          </li>
          <li class="divider"><hr></li>
        </template>
        <li v-for="item in items" :key="`dd-item-${item[idKey]}`" @click="selectItem(item)"
            :class="{ selected: isSelected(item), disabled: item.disabled }">
          <span v-if="!multiple">{{ item[labelKey] }}</span>
          <check-box v-else
                     @input="toggleItem(item, $event)"
                     :value="isSelected(item)">{{ item[labelKey] }}
          </check-box>
        </li>
      </template>
    </ul>
  </div>
</template>

<script>
  export default {
    name: 'DropDown',
    props: {
      title: {
        type: String,
        default: null,
        required: false,
      },
      titleAll: {
        type: String,
        default: null,
        required: false,
      },
      items: {
        required: true,
        validator: (prop) => Array.isArray(prop) || prop == null,
      },
      value: {
        required: true,
      },
      position: {
        type: String,
        default: 'left',
        validate: (p) => ['left', 'right'].indexOf(p) !== -1,
      },
      grow: {
        type: String,
        default: 'down',
        validate: (g) => ['up', 'down', 'md-up'].indexOf(g) !== -1,
      },
      multiple: {
        type: Boolean,
        default: false,
      },
      selectAll: {
        type: Boolean,
        default: false,
      },
      idKey: {
        type: String,
        default: 'id',
      },
      labelKey: {
        type: String,
        default: 'text',
      },
      disabled: Boolean,
    },
    data: () => ({
      // shown: false,
      lastEvent: null,
      preventClick: false,
      menuClass: '',
    }),
    created() {
      this.menuClass = `from-${this.position} ${this.multiple && 'dd-multiple'} grow-${this.grow}`;
    },
    computed: {
      ddTitle() {
        if (this.items == null || !this.items.length) return '';
        if (!this.multiple || (Array.isArray(this.value) && this.value.length === 1)) {
          const id = this.multiple ? this.value[0] : this.value;
          const sel = this.items.find((i) => i[this.idKey] === id);
          return sel != null ? sel[this.labelKey] : '';
        }
        const count = this.value.length;
        if (count === this.items.length) return this.$tc(this.titleAll);
        return this.$tc(this.title, count);
      },
      isSelectedAll() {
        return this.value.length === this.items.length;
      },
    },
    methods: {
      focused() {
        if (this.disabled) return;
        this.preventClick = this.lastEvent === 'm';
        this.$refs.menu.classList.remove('d-none');
        // this.shown = true;
      },
      blurred() {
        this.$refs.menu.classList.add('d-none');
        // this.shown = false;
      },
      clicked(e) {
        if (this.disabled) return;
        const { classList, parentNode: { classList: parentClassList } } = e.target;
        if (!classList.contains('drop-down') && !parentClassList.contains('drop-down')) return;
        if (!this.preventClick) {
          this.$refs.menu.classList.toggle('d-none');
          // this.shown = !this.shown;
        }
        this.preventClick = false;
      },
      isSelected(item) {
        if (!this.multiple) {
          return item[this.idKey] === this.value;
        }
        return this.value.indexOf(item[this.idKey]) !== -1;
      },
      toggleAllItems(on) {
        this.$nextTick(() => {
          if (!on) {
            this.value.splice(0, this.value.length);
          } else if (this.value.length !== this.items.length) {
            this.items.forEach(({ id }) => {
              if (!this.value.includes(id)) {
                this.value.push(id);
              }
            });
          }
        });
      },
      toggleItem(item, on) {
        this.$nextTick(() => {
          const { id } = item;
          if (on) {
            if (!this.value.includes(id)) {
              this.value.push(id);
            }
          } else {
            const idx = this.value.indexOf(id);
            if (idx !== -1) {
              this.value.splice(idx, 1);
            }
          }
        });
      },
      selectItem(item) {
        if (this.multiple || item.disabled) return;
        this.$emit('input', item[this.idKey]);
        this.$refs.menu.classList.add('d-none');
      },
    },
  };
</script>
