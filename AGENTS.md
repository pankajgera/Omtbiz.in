# Repository guide

## Stack and locations
- Laravel backend: `app/`, `routes/`, `database/`; PHP tests: `tests/Feature/`.
- Vue 3 with `@vue/compat`, Vuex, Vite, Bootstrap/Sass, and Tailwind (`tw:` prefix).
- Screens: `resources/js/views/`; shared controls/modals: `resources/js/components/base/`; API actions: `resources/js/store/modules/`.
- Theme tokens and legacy overrides: `resources/css/tailwind.css`; translations: `resources/js/plugins/en.json`.

## Frontend conventions
- Use Vue 3 named slots (`#slotName`); legacy `slot="..."` can silently disappear.
- Use `useVuelidate()` for changed forms; legacy validation wrappers can mask invalid fields.
- Reuse `--ui-*` colors. Verify dark/light themes and mobile spacing with actual app CSS.
- Dropdowns teleport to `body`; page-scoped ancestor selectors will not style their menus.
- Invoice rows use `invoices/Inventory.vue` and `InventorySelect.vue`, not the older `Item.vue` components. Rows are shared with estimates/orders; check those consumers.
- Keep row keys stable, synchronize cleared selections with parent data, and focus inputs after `$nextTick`. Verify Tab, Shift+Tab, Enter, cancellation, and save failures when changing pickers/modals.

## Verification and delivery
- Frontend build: `npm run production`; development: `npm run dev`.
- Backend: `php artisan test --filter=<RelevantTest>`; PHPUnit defaults to in-memory SQLite.
- Run `git diff --check`. Preserve unrelated working changes; keep temporary screenshots/logs outside the repo.
- State what was verified and whether changes are local or deployed. Deploy only when requested; list any live test records created for cleanup.
