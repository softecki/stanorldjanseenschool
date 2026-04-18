# Backend Blade to SPA Migration Matrix

This matrix tracks requested backend areas and their SPA/controller status.

| Requested area | Mapped backend endpoint/controller | SPA module/route | Status |
|---|---|---|---|
| `backend/my-profile/*` | `Backend\ProfileController` (`/my/profile`) | `App.jsx` profile pages (`/my/profile`) | Done |
| `backend/orders/*` | `OrderController` (`/order/*`) | `resources/js/spa/orders/OrderPages.jsx` (`/orders`) | Done |
| `backend/partials/*` | shared UI patterns (empty state/confirm) | `resources/js/spa/shared/UiStates.jsx` | Done |
| `backend/religion/*` | `Settings\ReligionController` (`/religions/*`) | `resources/js/spa/settings/ReligionPages.jsx` (`/settings/religions/*`) | Done |
| `backend/report/*` | report endpoints (`/report-*`, `/accounting/*`) | `App.jsx` report pages (`/reports/*`) | Done |
| `backend/roles/*` | `Backend\RoleController` (`/roles/*`) | `App.jsx` roles pages (`/roles`) | Done |
| `backend/session/*` | `Settings\SessionController` (`/sessions/*`) | `resources/js/spa/settings/SessionPages.jsx` (`/settings/sessions/*`) | Done |
| `backend/settings/*` | `Backend\SettingController` (`/settings/*`) | existing settings routes + JSON normalized controller | Done |
| `backend/shop_products/*` | `StorekeeperController` (`/products/*`) | mapped to goods/orders + existing product/account routes | Mapped |
| `backend/staff/*` | `Backend\UserController` + `Backend\RoleController` | existing users/roles SPA (`/users`, `/roles`) | Mapped |
| `backend/stock_overview/*` | inventory/order/storekeeper routes | mapped to orders/goods/accounting inventory routes | Mapped |
| `backend/student-info/*` | existing `StudentInfo\*` endpoints | `App.jsx` students pages (`/students*`) | Mapped |
| `backend/users/*` | `Backend\UserController` (`/users/*`) | `App.jsx` users pages | Done |
| `backend/vehicles/*` | `StorekeeperController` (`/vehicles/*`) | mapped to inventory/storekeeper flow (existing endpoint) | Mapped |
| `backend/*.blade.php` | top-level dashboard/auth/report/settings/menu functions | consolidated under SPA routes in `App.jsx` | Mapped |

