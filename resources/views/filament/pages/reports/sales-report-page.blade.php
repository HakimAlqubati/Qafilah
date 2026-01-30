<x-filament-panels::page>
    {{ $this->form }}

    @php
        $data = $this->reportData;
        $summary = $data['summary'];
    @endphp

    <style>
        /* =========================================================
           FUTURE DASHBOARD (1000 YEARS AHEAD)
           - No icons
           - Pure CSS
           - RTL-friendly
           - Dark mode first-class
           ========================================================= */

        /* -------- 0) Design Tokens -------- */
        :root {
            /* Surfaces */
            --fx-bg: #ffffff;
            --fx-surface: rgba(255, 255, 255, 0.72);
            --fx-surface-strong: rgba(255, 255, 255, 0.9);
            --fx-border: rgba(17, 24, 39, 0.10);
            --fx-border-strong: rgba(17, 24, 39, 0.16);

            /* Text */
            --fx-text: #0b1220;
            --fx-muted: rgba(11, 18, 32, 0.62);
            --fx-faint: rgba(11, 18, 32, 0.45);

            /* Shadows / Glow */
            --fx-shadow: 0 10px 28px rgba(0, 0, 0, 0.08), 0 2px 6px rgba(0, 0, 0, 0.06);
            --fx-shadow-hover: 0 16px 40px rgba(0, 0, 0, 0.12), 0 5px 14px rgba(0, 0, 0, 0.08);

            /* Radius & spacing */
            --fx-radius: 18px;
            --fx-radius-lg: 22px;

            /* Neon Accents (future spectrum) */
            --fx-neon-green: #2dffb3;
            --fx-neon-blue:  #55a7ff;
            --fx-neon-purple:#b07cff;
            --fx-neon-amber: #ffd36a;

            /* Gradients */
            --fx-grad-ambient: radial-gradient(1200px 600px at 85% -10%,
                    rgba(85, 167, 255, 0.18),
                    rgba(176, 124, 255, 0.12),
                    rgba(45, 255, 179, 0.08),
                    transparent 60%),
                radial-gradient(900px 500px at 10% 0%,
                    rgba(255, 211, 106, 0.14),
                    rgba(85, 167, 255, 0.08),
                    transparent 58%);

            --fx-grid: linear-gradient(to right, rgba(17, 24, 39, 0.06) 1px, transparent 1px),
                       linear-gradient(to bottom, rgba(17, 24, 39, 0.06) 1px, transparent 1px);

            /* Motion */
            --fx-ease: cubic-bezier(.2,.85,.25,1);
            --fx-fast: 160ms;
            --fx-med: 260ms;
        }

        /* Dark mode overrides (Filament uses .dark on root) */
        :is(.dark) {
            --fx-bg: #0a0b10;
            --fx-surface: rgba(24, 24, 27, 0.62);          /* zinc-ish glass */
            --fx-surface-strong: rgba(24, 24, 27, 0.86);
            --fx-border: rgba(255, 255, 255, 0.10);
            --fx-border-strong: rgba(255, 255, 255, 0.16);

            --fx-text: rgba(255, 255, 255, 0.92);
            --fx-muted: rgba(255, 255, 255, 0.62);
            --fx-faint: rgba(255, 255, 255, 0.45);

            --fx-shadow: 0 14px 34px rgba(0, 0, 0, 0.55), 0 4px 12px rgba(0, 0, 0, 0.35);
            --fx-shadow-hover: 0 18px 46px rgba(0, 0, 0, 0.62), 0 6px 18px rgba(0, 0, 0, 0.42);

            --fx-grid: linear-gradient(to right, rgba(255, 255, 255, 0.06) 1px, transparent 1px),
                       linear-gradient(to bottom, rgba(255, 255, 255, 0.06) 1px, transparent 1px);
        }

        /* Prefer reduced motion */
        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; animation: none !important; }
        }

        /* -------- 1) Layout Containers -------- */
        .dashboard-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            font-family: inherit;
            position: relative;

            /* Ambient futuristic backdrop inside the page content */
            padding: 0.25rem;
        }

        /* Subtle "future grid" background layer */
        .dashboard-container::before {
            content: "";
            position: absolute;
            inset: -18px -14px -14px -14px;
            z-index: 0;
            pointer-events: none;

            background:
                var(--fx-grad-ambient),
                var(--fx-grid);
            background-size:
                auto,
                22px 22px;
            background-position:
                center,
                center;

            opacity: 0.9;
            filter: blur(0px);
            mask-image: radial-gradient(60% 60% at 50% 15%, rgba(0,0,0,1), rgba(0,0,0,0) 72%);
        }

        /* Ensure content sits above backdrop */
        .dashboard-container > * {
            position: relative;
            z-index: 1;
        }

        /* Stats Grid: Responsive Layout */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            width: 100%;
        }

        /* Tables Grid: Two columns on large screens */
        .tables-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        @media (min-width: 1024px) {
            .tables-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* -------- 2) Component Styles (Futuristic Glass) -------- */

        /* --- Stat Cards --- */
        .stat-card {
            background: linear-gradient(180deg, var(--fx-surface-strong), var(--fx-surface));
            border: 1px solid var(--fx-border);
            border-radius: var(--fx-radius-lg);
            box-shadow: var(--fx-shadow);
            padding: 1.35rem 1.35rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            transition: transform var(--fx-med) var(--fx-ease),
                        box-shadow var(--fx-med) var(--fx-ease),
                        border-color var(--fx-med) var(--fx-ease);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            isolation: isolate; /* ensures glow layers don't bleed outside */
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--fx-shadow-hover);
            border-color: var(--fx-border-strong);
        }

        /* Neon edge + internal glow */
        .stat-card::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: var(--fx-radius-lg);
            pointer-events: none;

            /* thin neon edge via mask trick */
            padding: 1px;
            background: linear-gradient(135deg,
                rgba(255,255,255,0.10),
                rgba(255,255,255,0.02),
                rgba(255,255,255,0.10));
            -webkit-mask:
                linear-gradient(#000 0 0) content-box,
                linear-gradient(#000 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;

            opacity: 0.8;
        }

        .stat-card::after {
            content: "";
            position: absolute;
            inset: -40% -30% -45% -30%;
            pointer-events: none;
            opacity: 0.40;
            filter: blur(24px);
            transform: translateZ(0);
            transition: opacity var(--fx-med) var(--fx-ease);
            z-index: -1;
        }

        .stat-card:hover::after { opacity: 0.55; }

        /* Accent signature (right rail for RTL) */
        .stat-rail {
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 5px;
            background: rgba(255,255,255,0.08);
            pointer-events: none;
        }

        /* Revenue card (green) */
        .stat-card.revenue .stat-rail {
            background: linear-gradient(180deg, rgba(45, 255, 179, 0.95), rgba(45, 255, 179, 0.25));
        }
        .stat-card.revenue::after {
            background: radial-gradient(closest-side at 85% 35%,
                rgba(45, 255, 179, 0.48),
                rgba(45, 255, 179, 0.00) 60%);
        }

        /* Orders card (blue) */
        .stat-card.orders .stat-rail {
            background: linear-gradient(180deg, rgba(85, 167, 255, 0.95), rgba(85, 167, 255, 0.25));
        }
        .stat-card.orders::after {
            background: radial-gradient(closest-side at 85% 35%,
                rgba(85, 167, 255, 0.48),
                rgba(85, 167, 255, 0.00) 60%);
        }

        /* Average card (purple) */
        .stat-card.avg .stat-rail {
            background: linear-gradient(180deg, rgba(176, 124, 255, 0.95), rgba(176, 124, 255, 0.25));
        }
        .stat-card.avg::after {
            background: radial-gradient(closest-side at 85% 35%,
                rgba(176, 124, 255, 0.48),
                rgba(176, 124, 255, 0.00) 60%);
        }

        /* Items card (amber) */
        .stat-card.items .stat-rail {
            background: linear-gradient(180deg, rgba(255, 211, 106, 0.95), rgba(255, 211, 106, 0.25));
        }
        .stat-card.items::after {
            background: radial-gradient(closest-side at 85% 35%,
                rgba(255, 211, 106, 0.48),
                rgba(255, 211, 106, 0.00) 60%);
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--fx-muted);
            font-weight: 600;
            margin-bottom: 0.85rem;
            letter-spacing: 0.01em;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--fx-text);
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.01em;
            line-height: 1.1;
        }

        /* --- Tables (Card + Header) --- */
        .table-card {
            background: linear-gradient(180deg, var(--fx-surface-strong), var(--fx-surface));
            border: 1px solid var(--fx-border);
            border-radius: var(--fx-radius-lg);
            box-shadow: var(--fx-shadow);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            transition: transform var(--fx-med) var(--fx-ease),
                        box-shadow var(--fx-med) var(--fx-ease),
                        border-color var(--fx-med) var(--fx-ease);
            position: relative;
            isolation: isolate;
        }

        .table-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--fx-shadow-hover);
            border-color: var(--fx-border-strong);
        }

        .table-card::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            opacity: 0.35;
            background:
                radial-gradient(700px 240px at 15% 0%,
                    rgba(255, 255, 255, 0.10),
                    transparent 55%),
                radial-gradient(520px 200px at 85% 10%,
                    rgba(255, 255, 255, 0.06),
                    transparent 60%);
            z-index: -1;
        }

        .table-header {
            padding: 1rem 1.35rem;
            border-bottom: 1px solid var(--fx-border);
            background: linear-gradient(180deg,
                rgba(255, 255, 255, 0.06),
                rgba(255, 255, 255, 0.02));
        }

        .table-title {
            font-size: 1.02rem;
            font-weight: 800;
            color: var(--fx-text);
            letter-spacing: 0.02em;
        }

        /* --- Table --- */
        .table-scroll {
            overflow-x: auto;
            /* smooth edges on iOS */
            -webkit-overflow-scrolling: touch;
        }

        .custom-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.9rem;
            text-align: right;
            min-width: 560px; /* keeps structure nice when many columns */
        }

        .custom-table thead th {
            padding: 0.9rem 1.35rem;
            background: rgba(255, 255, 255, 0.04);
            color: var(--fx-muted);
            font-weight: 800;
            font-size: 0.74rem;
            text-transform: uppercase;
            letter-spacing: 0.10em;
            border-bottom: 1px solid var(--fx-border);
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .custom-table tbody td {
            padding: 1rem 1.35rem;
            color: var(--fx-text);
            border-bottom: 1px solid rgba(255, 255, 255, 0.00);
            position: relative;
        }

        /* row separators via background (more "future" than classic borders) */
        .custom-table tbody tr {
            background: linear-gradient(90deg,
                rgba(255, 255, 255, 0.00),
                rgba(255, 255, 255, 0.03),
                rgba(255, 255, 255, 0.00));
        }

        .custom-table tbody tr + tr td {
            border-top: 1px solid var(--fx-border);
        }

        .custom-table tbody tr:hover {
            background: linear-gradient(90deg,
                rgba(85, 167, 255, 0.00),
                rgba(85, 167, 255, 0.07),
                rgba(176, 124, 255, 0.00));
        }

        .custom-table tr:last-child td {
            border-bottom: none;
        }

        /* Numeric alignment helpers */
        .cell-center { text-align: center; }
        .cell-left { text-align: left; }
        .cell-right { text-align: right; }

        /* --- Badges --- */
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.22rem 0.70rem;
            border-radius: 9999px;
            font-size: 0.78rem;
            font-weight: 900;
            letter-spacing: 0.02em;
            border: 1px solid transparent;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            white-space: nowrap;
        }

        /* Default badge (blue-ish quantum glass) */
        .badge {
            background: rgba(85, 167, 255, 0.16);
            color: rgba(16, 61, 140, 0.95);
            border-color: rgba(85, 167, 255, 0.22);
        }
        :is(.dark) .badge {
            background: rgba(85, 167, 255, 0.14);
            color: rgba(205, 232, 255, 0.92);
            border-color: rgba(85, 167, 255, 0.22);
        }

        /* Vendor badge variant (purple) */
        .badge-vendor {
            background: rgba(176, 124, 255, 0.16);
            color: rgba(86, 24, 145, 0.95);
            border-color: rgba(176, 124, 255, 0.22);
        }
        :is(.dark) .badge-vendor {
            background: rgba(176, 124, 255, 0.14);
            color: rgba(240, 225, 255, 0.92);
            border-color: rgba(176, 124, 255, 0.22);
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 2.25rem;
            color: var(--fx-faint);
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        /* Small subtle index styling */
        .row-index {
            color: var(--fx-muted);
            width: 50px;
            font-weight: 800;
        }
    </style>

    <div class="dashboard-container">

        {{-- Stats Grid --}}
        <div class="stats-grid">
            <div class="stat-card revenue">
                <span class="stat-rail" aria-hidden="true"></span>
                <div class="stat-label">{{ __('lang.total_revenue') }}</div>
                <div class="stat-value">{{ $summary->getFormattedRevenue() }}</div>
            </div>

            <div class="stat-card orders">
                <span class="stat-rail" aria-hidden="true"></span>
                <div class="stat-label">{{ __('lang.total_orders') }}</div>
                <div class="stat-value">{{ number_format($summary->ordersCount) }}</div>
            </div>

            <div class="stat-card avg">
                <span class="stat-rail" aria-hidden="true"></span>
                <div class="stat-label">{{ __('lang.average_order_value') }}</div>
                <div class="stat-value">{{ $summary->getFormattedAverageValue() }}</div>
            </div>

            <div class="stat-card items">
                <span class="stat-rail" aria-hidden="true"></span>
                <div class="stat-label">{{ __('lang.total_items_sold') }}</div>
                <div class="stat-value">{{ number_format($summary->itemsCount) }}</div>
            </div>
        </div>

        {{-- Tables Grid --}}
        <div class="tables-grid">
            {{-- Products --}}
            <div class="table-card">
                <div class="table-header">
                    <div class="table-title">{{ __('lang.top_products') }}</div>
                </div>

                <div class="table-scroll">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th class="cell-center">#</th>
                                <th class="cell-right">{{ __('lang.product') }}</th>
                                <th class="cell-center">{{ __('lang.quantity') }}</th>
                                <th class="cell-left">{{ __('lang.revenue') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['top_products'] as $index => $product)
                                <tr>
                                    <td class="cell-center row-index">{{ $index + 1 }}</td>
                                    <td style="font-weight: 800;">{{ $product->productName }}</td>
                                    <td class="cell-center">
                                        <span class="badge">{{ $product->quantitySold }}</span>
                                    </td>
                                    <td class="cell-left" style="font-weight: 900;">{{ $product->totalRevenue }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="empty-state">{{ __('lang.no_data_available') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Vendors --}}
            <div class="table-card">
                <div class="table-header">
                    <div class="table-title">{{ __('lang.top_vendors') }}</div>
                </div>

                <div class="table-scroll">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th class="cell-right">{{ __('lang.vendor') }}</th>
                                <th class="cell-center">{{ __('lang.orders') }}</th>
                                <th class="cell-left">{{ __('lang.revenue') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['top_vendors'] as $vendor)
                                <tr>
                                    <td style="font-weight: 800;">{{ $vendor->vendorName }}</td>
                                    <td class="cell-center">
                                        <span class="badge badge-vendor">
                                            {{ $vendor->ordersCount }}
                                        </span>
                                    </td>
                                    <td class="cell-left" style="font-weight: 900;">{{ $vendor->totalRevenue }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="empty-state">{{ __('lang.no_data_available') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
