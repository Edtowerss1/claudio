import type { ChatStatus, UIMessage } from 'ai';
import { useEffect, useRef } from 'react';
import { Thinking } from '@/components/chat/thinking';
import { Markdown } from '@/components/chat/markdown';
import { RotateCcw } from 'lucide-react';
import { ErrorNote } from './error-note';

function textOf(message: UIMessage) {
    return message.parts
        .map((part) => (part.type === 'text' ? part.text : ''))
        .join('');
}

export function MessageList({
    messages,
    status,
    onRegenerate,
}: {
    messages: UIMessage[];
    status: ChatStatus;
    onRegenerate: () => void;
}) {
    const end = useRef<HTMLDivElement>(null);

    // new words are added to the end of the list, so scroll to the bottom when that happens
    useEffect(() => {
        end.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages]);

    return (
        <div className="flex-1 overflow-y-auto">
            <div className="mx-auto flex max-w-3xl flex-col gap-6 px-4 py-8">
                {messages.map((message) =>
                    message.role === 'user' ? (
                        <div
                            key={message.id}
                            className="ml-auto max-w-[80%] rounded-3xl bg-muted px-5 py-2.5 whitespace-pre-wrap"
                        >
                            {textOf(message)}
                        </div>
                    ) : (
                        <Markdown key={message.id}>{textOf(message)}</Markdown>
                    ),
                )}

                {status === 'ready' &&
                    messages.at(-1)?.role === 'assistant' && (
                        <button
                            type="button"
                            onClick={onRegenerate}
                            className="mt-4 flex items-center gap-1 self-start text-sm text-muted-foreground hover:text-foreground"
                        >
                            <RotateCcw className="size-3.5" /> Try again
                        </button>
                    )}

                {/* Sent, but no words back yet */}
                {status === 'submitted' && <Thinking />}
                {status === 'error' && <ErrorNote onRetry={onRegenerate} />}
                <div ref={end} />
            </div>
        </div>
    );
}
