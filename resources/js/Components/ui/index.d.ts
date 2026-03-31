// Barrel export type declarations for all UI components
// This allows TypeScript to resolve component types from .js barrel files

declare module '@/Components/ui/button' {
  export * from '@/Components/ui/button/index.js';
}

declare module '@/Components/ui/card' {
  export * from '@/Components/ui/card/index.js';
}

declare module '@/Components/ui/label' {
  export * from '@/Components/ui/label/index.js';
}

declare module '@/Components/ui/input' {
  export * from '@/Components/ui/input/index.js';
}

declare module '@/Components/ui/badge' {
  export * from '@/Components/ui/badge/index.js';
}

declare module '@/Components/ui/tabs' {
  export * from '@/Components/ui/tabs/index.js';
}

declare module '@/Components/ui/select' {
  export * from '@/Components/ui/select/index.js';
  export { default as SelectTrigger } from '@/Components/ui/select/SelectTrigger.vue';
  export { default as SelectContent } from '@/Components/ui/select/SelectContent.vue';
  export { default as SelectValue } from '@/Components/ui/select/SelectValue.vue';
}

declare module '@/Components/ui/separator' {
  export * from '@/Components/ui/separator/index.js';
}

declare module '@/Components/ui/skeleton' {
  export * from '@/Components/ui/skeleton/index.js';
}

declare module '@/Components/ui/scroll-area' {
  export * from '@/Components/ui/scroll-area/index.js';
}

declare module '@/Components/ui/command' {
  export * from '@/Components/ui/command/index.js';
}

declare module '@/Components/ui/table' {
  export * from '@/Components/ui/table/index.js';
}

declare module '@/Components/ui/dialog' {
  export * from '@/Components/ui/dialog/index.js';
}

declare module '@/Components/ui/dropdown-menu' {
  export * from '@/Components/ui/dropdown-menu/index.js';
}

declare module '@/Components/ui/sheet' {
  export * from '@/Components/ui/sheet/index.js';
}

declare module '@/Components/ui/data-table' {
  export * from '@/Components/ui/data-table/index.js';
}

declare module '@/Components/ui/collapsible' {
  export * from '@/Components/ui/collapsible/index.js';
}

declare module '@/Components/ui/alert-dialog' {
  export * from '@/Components/ui/alert-dialog/index.js';
}

declare module '@/Components/ui/switch' {
  export * from '@/Components/ui/switch/index.js';
}
