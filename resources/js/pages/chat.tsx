import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { Composer } from '@/components/chat/composer';
import { MessageList } from '@/components/chat/message-list';
import type { ChatMessage } from '@/components/chat/message-list';
import { index } from '@/routes/chat';
import { EmptyState } from '@/components/chat/empty-state';

export default function Chat() {
    const [messages, setMessages] = useState<ChatMessage[]>([]);

    function send(message: string) {
        setMessages((all) => [
            ...all,
            {
                id: crypto.randomUUID(),
                role: 'user',
                message,
            },
            {
                id: crypto.randomUUID(),
                role: 'assistant',
                message: 'This is a placeholder response.',
            },
        ]);
    }
    return (
        <>
            <Head title="Chat" />
            <div className="flex h-[calc(100svh-6rem)] flex-col">
                {messages.length ? (
                    <MessageList messages={messages} />
                ) : (
                    <EmptyState onPick={send} />
                )}
                <Composer onSend={send} />
            </div>
        </>
    );
}

Chat.layout = {
    breadcrumbs: [{ title: 'Chat', href: index() }],
};
