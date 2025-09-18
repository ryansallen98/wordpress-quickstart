// modal.ts

type ModalRefs = {
  trigger?: HTMLElement;
  dialog?: HTMLElement;
  [key: string]: HTMLElement | undefined;
};

type ModalState = {
  open: boolean;
  titleId: string;
  triggerEl: HTMLElement | null;

  // Alpine magic (typed so TS is happy)
  $refs: ModalRefs;
  $nextTick: (cb: () => void) => void;

  init(): void;
  openModal(): void;
  close(): void;
};

// default export = Alpine data factory
export default function modalComponent(modalId: string) {
  return {
    open: false,
    titleId: `${modalId}-title`,
    triggerEl: null as HTMLElement | null,

    // satisfy TS; Alpine overwrites these at runtime
    $refs: {} as ModalRefs,
    $nextTick: (cb: () => void) => cb(),

    init() {
      const t = this.$refs?.trigger ?? null;
      this.triggerEl =
        (t?.querySelector('[data-modal-open]') as HTMLElement) ||
        (t?.firstElementChild as HTMLElement) ||
        t ||
        null;

      if (this.triggerEl) {
        this.triggerEl.addEventListener('click', (e: Event) => {
          e.preventDefault();
          this.openModal();
        });
      }
    },

    openModal() {
      this.open = true;
      this.$nextTick(() => this.$refs?.dialog?.focus?.());
    },

    close() {
      this.open = false;
      this.$nextTick(() => this.triggerEl?.focus());
    },
  } as ModalState;
}