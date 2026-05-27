import { useEffect, useRef } from 'react';
import echo from '@/echo';

export function useEcho(
    channel: string,
    event: string,
    handler: (data: any) => void,
    deps: any[] = [],
) {
    const handlerRef = useRef(handler);
    handlerRef.current = handler;

    useEffect(() => {
        echo.channel(channel).listen(event, (data: any) => {
            handlerRef.current(data);
        });

        return () => {
            echo.leave(channel);
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [channel, event, ...deps]);
}

export function usePrivateEcho(
    channel: string,
    event: string,
    handler: (data: any) => void,
    deps: any[] = [],
) {
    const handlerRef = useRef(handler);
    handlerRef.current = handler;

    useEffect(() => {
        echo.private(channel).listen(event, (data: any) => {
            handlerRef.current(data);
        });

        return () => {
            echo.leave(channel);
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [channel, event, ...deps]);
}
