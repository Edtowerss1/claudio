import { Link, usePage } from '@inertiajs/react';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { show } from '@/routes/chat';
import { ChatActions } from './chat-actions';

export function NavChats() {
    const { conversations } = usePage().props;
    const { isCurrentUrl } = useCurrentUrl();

    if (!conversations.length) {
        return null;
    }

    return (
        <SidebarGroup>
            <SidebarGroupLabel>Chats</SidebarGroupLabel>
            <SidebarMenu>
                {conversations.map((conversation) => (
                    <SidebarMenuItem key={conversation.id}>
                        <SidebarMenuButton
                            asChild
                            isActive={isCurrentUrl(show(conversation.id))}
                        >
                            <Link href={show(conversation.id)} prefetch>
                                <span>{conversation.title}</span>
                            </Link>
                        </SidebarMenuButton>
                        <ChatActions conversation={conversation} />
                    </SidebarMenuItem>
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}
