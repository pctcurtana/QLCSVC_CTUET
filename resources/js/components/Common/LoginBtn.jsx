import React from 'react';
import { LoginOutlined } from '@ant-design/icons';

export const LoginBtn = ({
    children = "Hover me",
    onClick,
    htmlType = "button",
    loading,
    block,
    ...props
}) => {
    return (
        <button
            type={htmlType}
            onClick={onClick}
            disabled={loading}
            {...props}
            className={`
        group relative flex items-center justify-center gap-1 
        w-full py-[6px] border-4 border-transparent bg-inherit 
        rounded-[24px] font-semibold text-[rgb(36,67,128)] 
        shadow-[0_0_0_2px_rgb(36,67,128)] cursor-pointer overflow-hidden 
        transition-all duration-[800ms] ease-[cubic-bezier(0.23,1,0.32,1)] 
        hover:shadow-[0_0_0_12px_transparent] hover:text-[#ffffff] hover:rounded-[12px] 
        active:scale-95 active:shadow-[0_0_0_4px_rgb(36,67,128)]
        disabled:opacity-60 disabled:cursor-not-allowed
      `}
        >
            {/* Icon 1: Ban đầu ở bên phải */}
            <LoginOutlined
                className="
          absolute text-[18px] text-[rgb(36,67,128)] z-10 right-5 
          transition-all duration-[1000ms] ease-[cubic-bezier(0.23,1,0.32,1)] 
          group-hover:-right-[25%] group-hover:text-[#ffffff]
        "
            />

            {/* Vòng tròn lan tỏa màu xanh (Đã nâng lên 450px để phủ kín toàn Form) */}
            <span
                className="
          absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 
          w-5 h-5 bg-[rgb(36,67,128)] rounded-full opacity-0 
          transition-all duration-[1200ms] ease-[cubic-bezier(0.23,1,0.32,1)] 
          group-hover:w-[450px] group-hover:h-[450px] group-hover:opacity-100
        "
            />

            {/* Chữ hiển thị (Căn giữa hoàn hảo) */}
            <span
                className="
          relative z-10 -translate-x-2 
          transition-all duration-[1200ms] ease-[cubic-bezier(0.23,1,0.32,1)] 
          group-hover:translate-x-2
        "
            >
                {loading ? 'Đang xử lý...' : children}
            </span>

            {/* Icon 2: Ban đầu ẩn bên trái, khi hover chạy vào */}
            <LoginOutlined
                className="
          absolute text-[16px] text-[rgb(36,67,128)] z-10 -left-[25%] 
          transition-all duration-[1000ms] ease-[cubic-bezier(0.23,1,0.32,1)] 
          group-hover:left-5 group-hover:text-[#ffffff]
        "
            />
        </button>
    );
};