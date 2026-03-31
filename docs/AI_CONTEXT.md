# dailyPOS Project Context

## Overview

- Purpose: Advanced POS for owners (Inventory, CRM, AI Analysis).
- Tech Stack: Laravel (Backend), Vue 3 (Web), Flutter (Mobile), MySQL.

## Business Rules (CRITICAL)

1. Variants: One product can have many variants. Each variant has its own SKU and Stock.
2. HPP (COGS): Calculated based on the weighted average of stock purchases.
3. Loyalty: Customers get 1 point for every Rp 10.000 spent.
4. AI Pricing: Suggests price based on HPP + Margin + Sales Velocity.

## Key File Locations

- Logic HPP: `app/Services/InventoryService.php`
- UI Cashier: `resources/js/Pages/Cashier/Index.vue`
- API Flutter: `routes/api.php`
