@props(['type' => 'single'])

<div
  {{ $attributes }}
  role="presentation"
  x-data="{
    type: @js($type),
    open: @js($type === 'single' ? null : []),
    isOpen(id){ return this.type === 'single' ? this.open === id : this.open.includes(id) },
    toggle(id){
      if(this.type === 'single'){ this.open = this.open === id ? null : id }
      else { this.open = this.isOpen(id) ? this.open.filter(i => i !== id) : [...this.open, id] }
    },
    focusList: [],
    registerTrigger(el){ if (!this.focusList.includes(el)) this.focusList.push(el) },
    moveFocus(delta, current){
      const i = this.focusList.indexOf(current); if(i === -1) return;
      const next = (i + delta + this.focusList.length) % this.focusList.length;
      this.focusList[next]?.focus();
    }
  }"
>
  {{ $slot }}
</div>