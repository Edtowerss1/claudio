import { useChat } from '@ai-sdk/react';
import { Head, router } from '@inertiajs/react';
import { DefaultChatTransport, UIMessage } from 'ai';
import { useState } from 'react';
import { Composer } from '@/components/chat/composer';
import { MessageList } from '@/components/chat/message-list';
import { xsrfHeader } from '@/lib/csrf';
import { EmptyState } from '@/components/chat/empty-state';
import { index, show, stream } from '@/routes/chat';

type Props = {
    id: string;
    messages: UIMessage[];
};

export default function ChatPage(props: Props) {
    return <Chat key={props.id} {...props} />;
}

function Chat({ id, messages: savedMessages }: Props) {
    const [smartest, setSmartest] = useState(false);
    const { messages, sendMessage, regenerate, status, stop } = useChat({
        id,
        messages: savedMessages,
        transport: new DefaultChatTransport({
            api: stream.url(),
            headers: xsrfHeader(),
        }),
        onFinish: ({ isError }) => {
            // Keep the failed chat state so the user can retry.
            if (!isError) {
                router.reload({ only: ['conversations'] });
            }
        },
    });

    function send(message: string) {
        void sendMessage({ text: message }, {body: { smartest } });

        if (!messages.length) {
            router.replace({ url: show.url(id), preserveState: true });
        }
    }

    return (
        <>
            <Head title="Chat" />
            <div className="flex h-[calc(100svh-6rem)] flex-col">
                {messages.length ? (
                    <MessageList
                        messages={messages}
                        status={status}
                        onRegenerate={() => void regenerate({body: { smartest } })}
                    />
                ) : (
                    <EmptyState onPick={send} />
                )}
                <Composer
                    onSend={send}
                    onStop={stop}
                    busy={status === 'submitted' || status === 'streaming'}
                    smartest={smartest}
                    onSmartestChange={setSmartest}
                />
            </div>
        </>
    );
}

ChatPage.layout = {
    breadcrumbs: [{ title: 'Chat', href: index() }],
};
