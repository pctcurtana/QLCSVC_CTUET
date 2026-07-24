import { useEffect, useRef } from 'react';

/**
 * Custom React hook lắng nghe realtime thống kê từ Pusher.
 *
 * Khi backend broadcast event 'thong-ke.updated' trên private channel 'dashboard',
 * hook sẽ gọi callback với { updatedKeys, snapshots }.
 *
 * @param {Function} onUpdate - Callback nhận { updatedKeys: string[], snapshots: { [key: string]: any } }
 * @param {boolean} [enabled=true] - Bật/tắt lắng nghe
 */
const useThongKeChannel = (onUpdate, enabled = true) => {
    const callbackRef = useRef(onUpdate);

    // Luôn giữ ref mới nhất để tránh stale closure
    useEffect(() => {
        callbackRef.current = onUpdate;
    }, [onUpdate]);

    useEffect(() => {
        if (!enabled || !window.Echo) {
            return;
        }

        const channel = window.Echo.private('dashboard');

        channel.listen('.thong-ke.updated', (event) => {
            if (callbackRef.current) {
                callbackRef.current({
                    updatedKeys: event.updatedKeys || [],
                });
            }
        });


        return () => {
            channel.stopListening('.thong-ke.updated');
            window.Echo.leave('dashboard');
        };
    }, [enabled]);
};

export default useThongKeChannel;
