import React from 'react';
import { Card } from 'antd';

const KpiCard = ({ title, value, icon, color, footerText = 'Dữ liệu mới nhất' }) => {
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
            <div
                className="mb-2 text-[24px] font-extrabold leading-none tracking-[-0.05em] text-[#0f172a]"
                style={{
                    fontFamily: "'Plus Jakarta Sans', sans-serif",
                }}
            >
                {value}
            </div>

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
