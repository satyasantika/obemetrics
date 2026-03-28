<style>
    /* ── Shared Dashboard Stats Styles ── */

    .dashboard-stats-container {
        background: transparent;
        border: 0;
        border-radius: 0;
        box-shadow: none;
    }

    .dashboard-stats-header {
        background-color: transparent;
        border-bottom: 1px solid rgba(96, 84, 68, 0.12) !important;
        padding: 0.6rem 0 1rem;
        margin-bottom: 1.1rem;
    }

    .dashboard-stats-content {
        padding: 0;
    }

    .dashboard-stats-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--panel-text);
        margin-bottom: 0.3rem;
    }

    .dashboard-stats-subtitle {
        font-size: 0.88rem;
        color: var(--panel-muted);
    }

    /* ── Grid Layout ── */
    .dashboard-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 1.2rem;
    }

    /* ── Link Wrapper ── */
    .dashboard-stat-link {
        display: block;
        text-decoration: none;
        color: inherit;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .dashboard-stat-link:hover {
        transform: translateY(-4px);
    }

    .dashboard-stat-link:hover .dashboard-stat-card {
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
        border-color: var(--panel-accent);
    }

    .dashboard-stat-link:hover .dashboard-stat-icon {
        transform: scale(1.1);
    }

    /* ── Card Container ── */
    .dashboard-stat-card {
        border: 1px solid rgba(96, 84, 68, 0.1);
        border-radius: 1.15rem;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(250, 247, 241, 0.8));
        height: 100%;
        display: grid;
        grid-template-columns: 3.6rem 1fr;
        align-items: start;
        column-gap: 1rem;
        padding: 1.35rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(8px);
        box-shadow: 0 4px 12px rgba(82, 63, 42, 0.06);
    }

    .dashboard-stat-card-content {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-width: 0;
    }

    /* ── Icon Container ── */
    .dashboard-stat-icon {
        flex-shrink: 0;
        width: 3.6rem;
        height: 3.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.7rem;
        border-radius: 0.9rem;
        background: var(--panel-accent-soft);
        color: var(--panel-accent-strong);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ── Label ── */
    .dashboard-stat-label {
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--panel-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.4rem;
    }

    /* ── Value ── */
    .dashboard-stat-value {
        font-size: 1.8rem;
        line-height: 1;
        letter-spacing: -0.02em;
        font-weight: 800;
        color: var(--panel-accent-strong);
        margin-bottom: 0.65rem;
    }

    /* ── Footer Link ── */
    .dashboard-stat-footer {
        margin-top: auto;
        font-size: 0.8rem;
        color: var(--panel-accent);
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    /* ── Mobile Responsiveness ── */
    @media (max-width: 767.98px) {
        .dashboard-stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.8rem;
        }

        .dashboard-stat-card {
            grid-template-columns: 1fr;
            justify-items: start;
            row-gap: 0.7rem;
            padding: 1rem;
        }

        .dashboard-stat-icon {
            width: 2.8rem;
            height: 2.8rem;
            font-size: 1.35rem;
            flex-shrink: 0;
        }

        .dashboard-stat-card-content {
            width: 100%;
        }

        .dashboard-stat-label {
            font-size: 0.74rem;
            margin-bottom: 0.35rem;
        }

        .dashboard-stat-value {
            font-size: 1.35rem;
            margin-bottom: 0.45rem;
        }

        .dashboard-stats-title {
            font-size: 1rem;
        }

        .dashboard-stats-subtitle {
            font-size: 0.8rem;
        }

        .dashboard-stats-header {
            padding: 0.4rem 0 0.75rem;
            margin-bottom: 0.9rem;
        }
    }
</style>
