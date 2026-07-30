import React from 'react';
import { Card, Tooltip } from 'antd';
import CountUp from 'react-countup';

const parseCountUpValue = (val) => {
    if (val === null || val === undefined) return null;
    if (typeof val === 'number') {
        return {
            end: val,
            decimals: Number.isInteger(val) ? 0 : 2,
            separator: '.',
            decimal: ',',
            prefix: '',
            suffix: '',
        };
    }
    if (typeof val === 'string') {
        const trimmed = val.trim();
        if (!trimmed || trimmed === '—' || trimmed === 'N/A') return null;

        const match = trimmed.match(/^([^0-9\-+]*)([\d.,]+)(.*)$/);
        if (!match) return null;

        const [, rawPrefix, numStr, rawSuffix] = match;

        let separator = '.';
        let decimal = ',';
        let decimals = 0;
        let end = 0;

        const hasTextUnit = /(tỉ|tỷ|triệu)/i.test(rawSuffix);

        if (hasTextUnit && (numStr.includes('.') || numStr.includes(','))) {
            const normalized = numStr.replace(',', '.');
            end = parseFloat(normalized);
            const decParts = normalized.split('.');
            decimals = decParts[1] ? decParts[1].length : 0;
            separator = '.';
            decimal = ',';
        } else {
            if (numStr.includes('.') && numStr.includes(',')) {
                if (numStr.lastIndexOf('.') > numStr.lastIndexOf(',')) {
                    separator = ',';
                    decimal = '.';
                    end = parseFloat(numStr.replace(/,/g, ''));
                } else {
                    separator = '.';
                    decimal = ',';
                    end = parseFloat(numStr.replace(/\./g, '').replace(',', '.'));
                }
            } else if (numStr.includes('.')) {
                const parts = numStr.split('.');
                if (parts.length > 2 || (parts.length === 2 && parts[1].length === 3)) {
                    separator = '.';
                    end = parseFloat(numStr.replace(/\./g, ''));
                    decimals = 0;
                } else {
                    end = parseFloat(numStr);
                    decimals = parts[1].length;
                    decimal = '.';
                }
            } else if (numStr.includes(',')) {
                const parts = numStr.split(',');
                if (parts.length > 2 || (parts.length === 2 && parts[1].length === 3)) {
                    separator = ',';
                    end = parseFloat(numStr.replace(/,/g, ''));
                    decimals = 0;
                } else {
                    end = parseFloat(numStr.replace(',', '.'));
                    decimals = parts[1].length;
                    decimal = ',';
                }
            } else {
                end = parseFloat(numStr);
                decimals = 0;
            }
        }

        if (isNaN(end)) return null;

        return {
            end,
            decimals,
            separator,
            decimal,
            prefix: rawPrefix,
            suffix: rawSuffix,
        };
    }
    return null;
};

const KpiCard = ({ title, value, icon, color, footerText = 'Dữ liệu mới nhất', tooltip }) => {
    const parsed = parseCountUpValue(value);

    const valueContent = parsed ? (
        <CountUp
            end={parsed.end}
            duration={1.5}
            decimals={parsed.decimals}
            decimal={parsed.decimal || ','}
            separator={parsed.separator}
            prefix={parsed.prefix}
            suffix={parsed.suffix}
            enableReinitialize={true}
            preserveValue={true}
        />
    ) : (
        value
    );

    const valueNode = (
        <div
            className="mb-2 text-[24px] font-extrabold leading-none tracking-[-0.05em] text-[#0f172a]"
            style={{
                fontFamily: "'Plus Jakarta Sans', sans-serif",
            }}
        >
            {valueContent}
        </div>
    );

    return (
        <Card
            bordered={false}
            className="
                relative
                overflow-hidden
                h-full
                rounded-[18px]
                border
                border-white/35
                bg-white/55
                backdrop-blur-xl
                shadow-[0_6px_20px_rgba(15,23,42,.04)]
            "
            styles={{
                body: {
                    padding: 16,
                },
            }}
        >
            {/* Glow */}
            <div
                className="absolute -top-6 -right-6 h-20 w-20 rounded-full blur-3xl"
                style={{
                    background: `${color}15`,
                }}
            />

            {/* Header */}
            <div className="mb-3 flex items-center justify-between">
                <div className="text-[10px] font-bold uppercase tracking-[0.12em] text-slate-500">
                    {title}
                </div>

                <div
                    className="flex h-9 w-9 items-center justify-center rounded-xl"
                    style={{
                        background: `${color}15`,
                        color,
                    }}
                >
                    {icon}
                </div>
            </div>

            {/* Value */}
            {tooltip ? (
                <Tooltip title={tooltip}>
                    {valueNode}
                </Tooltip>
            ) : valueNode}

            {/* Footer */}
            {footerText && (
                <div className="flex items-center gap-1.5">
                    <div
                        className="h-1.5 w-1.5 rounded-full"
                        style={{
                            background: color,
                        }}
                    />

                    <span className="text-[10px] font-semibold uppercase tracking-[0.05em] text-slate-400">
                        {footerText}
                    </span>
                </div>
            )}
        </Card>
    );
};

export default KpiCard;


