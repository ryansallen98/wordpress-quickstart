type Primitive = string | number | boolean | null;
type Value = Primitive;
type InitialValue = Value | undefined;

export interface SelectProps {
  name?: string | null;
  searchable?: boolean;
  placeholder?: string;
  value?: InitialValue;
  maxHeight?: string;
}

export interface SelectItem {
  value: Value;
  label: string | number;
  disabled?: boolean;
  id?: string;
  el?: HTMLElement | null;
  displayHTML?: string;
}

type AlpineMagics = {
  $nextTick: (cb: () => void) => void;
  $dispatch: (name: string, detail?: any) => void;
};

type SelectState = AlpineMagics & {
  open: boolean;
  query: string;
  searchable: boolean;
  placeholder: string;
  name: string | null | undefined;
  items: SelectItem[];
  activeIndex: number;
  selected: Value | null;
  triggerId: string;

  register(this: SelectState, it: SelectItem, el?: HTMLElement | null): void;
  unregister(this: SelectState, valueToRemove: Value): void;
  filtered(this: SelectState): SelectItem[];
  isSelected(this: SelectState, val: Value): boolean;
  toggleOpen(this: SelectState): void;
  openMenu(this: SelectState): void;
  close(this: SelectState): void;
  setActiveByIndex(this: SelectState, i: number): void;
  moveActive(this: SelectState, delta: number): void;
  scrollActiveIntoView(this: SelectState): void;
  selectValue(this: SelectState, val: Value): void;
  selectActive(this: SelectState): void;
  clear(this: SelectState): void;
  displayLabels(this: SelectState): Array<string | number>;
  refreshActive(this: SelectState): void;
  activeOptionId(this: SelectState): string | null;

  /** 🔽 New: returns HTML for the selected item's trigger display */
  selectedDisplayHTML(this: SelectState): string;
};

export default function selectComponent(props: SelectProps = {}) {
  const {
    searchable = false,
    placeholder = 'Select…',
    name = null,
    value = null,
  } = props;

  const state: SelectState = {
    open: false,
    query: '',
    searchable,
    placeholder,
    name,
    items: [],
    activeIndex: -1,
    selected: value ?? null,
    triggerId: 'trigger-' + Math.random().toString(36).slice(2, 9),

    $nextTick: (() => {}) as any,
    $dispatch: (() => {}) as any,

    register(it, el) {
      it.el = el;

      // ensure DOM id for aria-activedescendant
      it.id = 'opt-' + Math.random().toString(36).slice(2, 9);
      if (!el?.id) el && (el.id = it.id);

      // 🔽 Capture display HTML: prefer [data-select-display], else the whole option innerHTML
      try {
        const displayEl = el?.querySelector?.('[data-select-display]') as HTMLElement | null;
        const html = (displayEl?.innerHTML ?? el?.innerHTML ?? '').trim();
        it.displayHTML = html;
      } catch {
        it.displayHTML = '';
      }

      this.items.push(it);
      this.refreshActive();
    },

    activeOptionId() {
      const list = this.filtered();
      const it = list[this.activeIndex];
      return it?.el?.id ?? null;
    },

    unregister(valueToRemove) {
      this.items = this.items.filter((i) => i.value !== valueToRemove);
      this.refreshActive();
    },

    filtered() {
      const q = this.query.trim().toLowerCase();
      return q
        ? this.items.filter((i) => String(i.label).toLowerCase().includes(q))
        : this.items;
    },

    isSelected(val) {
      return this.selected === val;
    },

    toggleOpen() {
      this.open ? this.close() : this.openMenu();
    },
    openMenu() {
      this.open = true;
      if (this.activeIndex < 0 && this.filtered().length) this.activeIndex = 0;
      this.$nextTick(() => this.scrollActiveIntoView());
    },
    close() {
      this.open = false;
      this.query = '';
      this.activeIndex = -1;
    },

    setActiveByIndex(i) {
      const list = this.filtered();
      if (!list.length) {
        this.activeIndex = -1;
        return;
      }
      this.activeIndex = Math.max(0, Math.min(i, list.length - 1));
      this.$nextTick(() => this.scrollActiveIntoView());
    },
    moveActive(delta: number) {
      const list = this.filtered();
      if (!list.length) return;
      let next = this.activeIndex;
      for (let i = 0; i < list.length; i++) {
        next = (next + delta + list.length) % list.length;
        if (!list[next]?.disabled) break;
      }
      this.activeIndex = next;
      this.$nextTick(() => this.scrollActiveIntoView());
    },
    scrollActiveIntoView() {
      const list = this.filtered();
      const it = list[this.activeIndex];
      if (it?.el) it.el.scrollIntoView({ block: 'nearest' });
    },

    selectValue(val) {
      this.selected = val;
      this.close();
      this.$dispatch('select:change', {
        name: this.name,
        value: this.selected,
      });
    },

    selectActive() {
      const list = this.filtered();
      if (list[this.activeIndex])
        this.selectValue(list[this.activeIndex].value);
    },

    clear() {
      this.selected = null;
      this.$dispatch('select:change', {
        name: this.name,
        value: this.selected,
      });
    },

    displayLabels() {
      const it = this.items.find((i) => i.value === this.selected);
      return it ? [it.label] : [];
    },

    /** 🔽 New */
    selectedDisplayHTML() {
      const it = this.items.find((i) => i.value === this.selected);
      return it?.displayHTML || '';
    },

    refreshActive() {
      const len = this.filtered().length;
      if (this.activeIndex >= len) this.activeIndex = len - 1;
    },
  };

  return state;
}