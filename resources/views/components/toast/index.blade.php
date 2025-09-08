<!-- TOASTS: mount once in your app layout -->
<div
  x-data="toastCenter()"
  x-init="init()"
  class="pointer-events-none fixed right-4 bottom-4 z-[9999] w-[calc(100vw-2rem)] space-y-3 sm:w-96"
  aria-live="polite"
  aria-atomic="true"
>
  <template x-for="t in toasts" :key="t.id">
    <div
      x-show="t.show"
      x-transition:enter="transition duration-200 ease-out"
      x-transition:enter-start="translate-y-2 opacity-0"
      x-transition:enter-end="translate-y-0 opacity-100"
      x-transition:leave="transition duration-150 ease-in"
      x-transition:leave-start="translate-x-0 opacity-100"
      x-transition:leave-end="translate-x-30 opacity-0"
      class="pointer-events-auto rounded-md bg-card p-4 text-foreground shadow-lg border"
      :class="typeClasses(t.type)"
      role="status"
      @mouseenter="pause(t.id)"
      @mouseleave="resume(t.id)"
    >
      <div class="flex items-start gap-3">
        <!-- icon slot -->
        <div class="mt-0.5">
          <template x-if="t.type === 'success'">
            <span aria-hidden="true">
              <x-lucide-check-circle class="h-5 w-5 text-success" />
            </span>
          </template>
          <template x-if="t.type === 'error'">
            <span aria-hidden="true">
              <x-lucide-triangle-alert class="h-5 w-5 text-destructive" />
            </span>
          </template>
          <template x-if="t.type === 'info'">
            <span aria-hidden="true">
              <x-lucide-info class="h-5 w-5 text-info" />
            </span>
          </template>
        </div>

        <div class="min-w-0 flex-1">
          <p class="text-sm" x-text="t.title || ''" x-show="t.title"></p>
          <p
            class="text-sm"
            :class="{'mt-0.5': t.title}"
            x-text="t.text"
          ></p>

          <!-- optional actions -->
          <div class="mt-2 flex gap-3" x-show="t.actions?.length">
            <template x-for="(a, i) in t.actions" :key="i">
              <button
                class="text-sm underline underline-offset-2 hover:no-underline"
                @click="handleAction(t.id, a)"
                x-text="a.label"
              ></button>
            </template>
          </div>

          <!-- progress (optional) -->
          <div
            class="mt-2 h-1 w-full overflow-hidden rounded"
            x-show="t.progress"
          >
            <div
              class="h-full"
              :class="barClasses(t.type)"
              :style="`width:${t.progress}%`"
            ></div>
          </div>
        </div>

        <button
          type="button"
          class="shrink-0 rounded-full text-sm/5 text-muted-foreground hover:text-foreground cursor-pointer"
          @click="dismiss(t.id)"
          aria-label="Dismiss"
        >
          <x-lucide-x class="h-4 w-4" aria-hidden="true" />
        </button>
      </div>
    </div>
  </template>
</div>
