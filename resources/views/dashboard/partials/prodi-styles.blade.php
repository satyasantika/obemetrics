<style>
    .prodi-stats-container {
        background: linear-gradient(135deg, rgba(255, 251, 246, 0.9), rgba(248, 241, 230, 0.7));
        border: 1px solid rgba(96, 84, 68, 0.08);
        border-radius: 1.25rem;
    }

    .prodi-stats-header {
        background-color: transparent;
        border-bottom: 1px solid rgba(96, 84, 68, 0.1) !important;
    }

    .prodi-stats-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--panel-text);
        margin-bottom: 0.3rem;
    }

    .prodi-stats-subtitle {
        font-size: 0.88rem;
        color: var(--panel-muted);
    }

    /* ── Grid Layout ── */
    .prodi-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.2rem;
    }

    /* ── Link Wrapper ── */
    .prodi-stat-link {
        display: block;
        text-decoration: none;
        color: inherit;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .prodi-stat-link:hover {
        transform: translateY(-4px);
    }

    .prodi-stat-link:hover .prodi-stat-card {
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
        border-color: var(--panel-accent);
    }

    .prodi-stat-link:hover .prodi-stat-icon {
        transform: scale(1.1);
    }

    /* ── Card Container ── */
    .prodi-stat-card {
        border: 1px solid rgba(96, 84, 68, 0.1);
        border-radius: 1.15rem;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(250, 247, 241, 0.8));
        height: 100%;
        display: flex;
        flex-direction: row;
        align-items: flex-start;
        gap: 1.2rem;
        padding: 1.8rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(8px);
        box-shadow: 0 4px 12px rgba(82, 63, 42, 0.06);
    }

    .prodi-stat-card-content {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-width: 0;
    }

    /* ── Icon Container ── */
    .prodi-stat-icon {
        flex-shrink: 0;
        width: 4rem;
        height: 4rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        border-radius: 0.9rem;
        background: var(--panel-accent-soft);
        color: var(--panel-accent-strong);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ── Label ── */
    .prodi-stat-label {
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--panel-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.4rem;
    }

    /* ── Value ── */
    .prodi-stat-value {
        font-size: 2rem;
        line-height: 1;
        letter-spacing: -0.02em;
        font-weight: 800;
        color: var(--panel-accent-strong);
        margin-bottom: 0.8rem;
    }

    /* ── Footer Link ── */
    .prodi-stat-footer {
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
        .prodi-stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .prodi-stat-card {
            padding: 1.2rem;
            flex-direction: row;
            gap: 1rem;
        }

        .prodi-stat-icon {
            width: 3.2rem;
            height: 3.2rem;
            font-size: 1.55rem;
            flex-shrink: 0;
        }

        .prodi-stat-label {
            font-size: 0.8rem;
            margin-bottom: 0.45rem;
        }

        .prodi-stat-value {
            font-size: 1.6rem;
        }

        .prodi-stats-title {
            font-size: 1rem;
        }

        .prodi-stats-subtitle {
            font-size: 0.8rem;
        }
    }
</style>
