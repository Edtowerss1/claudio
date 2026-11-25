import { ArrowUp } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';

export function Composer({
    onSend,
    busy,
}: {
    onSend: (message: string) => void;
    busy: boolean;
}) {
    const [message, setMessage] = useState('');

    function submit() {
        if (!message.trim() || busy) return;
        onSend(message.trim());
        setMessage('');
    }

    return (
        <form
            onSubmit={(event) => {
                event.preventDefault();
                submit();
            }}
            className="mx-auto mb-4 flex w-full max-w-3xl items-end gap-2 rounded-3xl border bg-background p-2 shadow-sm"
        >
            <textarea
                name="message"
                value={message}
                onChange={(event) => setMessage(event.target.value)}
                onKeyDown={(event) => {
                    // Enter sends, shift+Enter starts a new line
                    if (event.key === 'Enter' && !event.shiftKey) {
                        event.preventDefault();
                        submit();
                    }
                }}
                rows={1}
                placeholder="Type your message..."
                className="field-sizing-content max-h-48 flex-1 resize-none bg-transparent px-3 py-2 outline-none"
            />
            <Button
                type="submit"
                size="icon"
                className="rounded-full"
                disabled={!message.trim()}
            >
                <ArrowUp />
            </Button>
        </form>
    );
}
