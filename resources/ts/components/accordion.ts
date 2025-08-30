export type AccordionType = 'single' | 'multiple';

export interface AccordionProps {
  type?: AccordionType;
}

export interface AccordionState {
  // props
  type: AccordionType;

  // state
  open: number | string | null | Array<number | string>;
  focusList: HTMLElement[];

  // methods
  isOpen(id: number | string): boolean;
  toggle(id: number | string): void;
  registerTrigger(el: HTMLElement): void;
  moveFocus(delta: number, current: HTMLElement): void;
}

export default function accordionComponent(
  { type = 'single' }: AccordionProps = {},
): AccordionState {
  return {
    type,
    open: type === 'single' ? null : [],
    focusList: [],

    isOpen(id) {
      return this.type === 'single'
        ? this.open === id
        : (this.open as Array<number | string>).includes(id);
    },

    toggle(id) {
      if (this.type === 'single') {
        this.open = this.open === id ? null : id;
      } else {
        const list = this.open as Array<number | string>;
        this.open = this.isOpen(id)
          ? list.filter(i => i !== id)
          : [...list, id];
      }
    },

    registerTrigger(el) {
      if (!this.focusList.includes(el)) this.focusList.push(el);
    },

    moveFocus(delta, current) {
      const i = this.focusList.indexOf(current);
      if (i === -1 || this.focusList.length === 0) return;
      const next = (i + delta + this.focusList.length) % this.focusList.length;
      this.focusList[next]?.focus();
    },
  };
}