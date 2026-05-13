import React, { useEffect, useRef, useState } from 'react';

const DEFAULT_DURATION_MS = 3000;

function easeOutCubic(t) {
    return 1 - (1 - t) ** 3;
}

function parseMoneyTarget(value) {
    if (value === null || value === undefined || value === '') return NaN;
    const n = Number(value);
    return Number.isFinite(n) ? n : NaN;
}

/**
 * Counts from 0 to `value` over `durationMs` (default 3s) with ease-out easing.
 * Respects prefers-reduced-motion.
 */
export function AnimatedMoney({
    value,
    durationMs = DEFAULT_DURATION_MS,
    className = '',
    minimumFractionDigits = 2,
    maximumFractionDigits = 2,
}) {
    const end = parseMoneyTarget(value);
    const [display, setDisplay] = useState(0);
    const rafRef = useRef(0);

    useEffect(() => {
        if (!Number.isFinite(end)) {
            setDisplay(0);
            return;
        }

        if (typeof window !== 'undefined' && window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) {
            setDisplay(end);
            return;
        }

        let startTs = null;
        const from = 0;

        const tick = (ts) => {
            if (startTs === null) startTs = ts;
            const t = Math.min((ts - startTs) / durationMs, 1);
            const eased = easeOutCubic(t);
            setDisplay(from + (end - from) * eased);
            if (t < 1) {
                rafRef.current = requestAnimationFrame(tick);
            } else {
                setDisplay(end);
            }
        };

        setDisplay(0);
        rafRef.current = requestAnimationFrame(tick);
        return () => cancelAnimationFrame(rafRef.current);
    }, [end, durationMs]);

    if (!Number.isFinite(end)) {
        return '—';
    }

    const formatOptions = { minimumFractionDigits, maximumFractionDigits };
    const text = end.toLocaleString(undefined, formatOptions);
    const shown = display.toLocaleString(undefined, formatOptions);

    return (
        <span className={`tabular-nums ${className}`.trim()} title={text}>
            {shown}
        </span>
    );
}
