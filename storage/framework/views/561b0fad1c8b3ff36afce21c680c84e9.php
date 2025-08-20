
<script>
function enhanceChatWithStreaming() {
    return {
        streamAIResponse: async function(message, vectorMemoryId) {
            console.log('Starting streamAIResponse', { message, vectorMemoryId });
            
            // Prepare streaming message
            const streamingMessage = {
                id: 'streaming-' + Date.now(),
                role: 'assistant',
                content: '',
                created_at: new Date().toISOString(),
                isStreaming: true
            };
            
            this.messages.push(streamingMessage);
            const messageIndex = this.messages.length - 1;
            
            // Create form data with stream flag
            const formData = new FormData();
            formData.append('message', message);
            formData.append('conversation_id', this.currentConversationId);
            formData.append('stream', 'true');
            if (vectorMemoryId) {
                formData.append('vector_memory_id', vectorMemoryId);
            }
            
            try {
                console.log('Sending request with streaming...', formData.get('stream'));
                const response = await fetch('/chat/send', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });
                
                console.log('Response received:', response.status, response.headers.get('content-type'));
                
                // Check if we got a streaming response
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('text/event-stream')) {
                    console.log('Falling back to non-streaming response...');
                    // Fallback to non-streaming
                    const data = await response.json();
                    if (data.success) {
                        this.messages[messageIndex].content = data.response;
                        this.messages[messageIndex].isStreaming = false;
                        this.messages[messageIndex].id = data.message_id;
                        await this.loadConversations();
                    } else {
                        throw new Error(data.error || 'Unknown error');
                    }
                    return;
                }
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
                console.log('Starting to read stream...');
                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let buffer = '';
                let messageId = null;
                
                while (true) {
                    const { done, value } = await reader.read();
                    if (done) {
                        console.log('Stream ended');
                        break;
                    }
                    
                    buffer += decoder.decode(value, { stream: true });
                    const lines = buffer.split('\n');
                    buffer = lines.pop() || '';
                    
                    for (const line of lines) {
                        if (line.startsWith('event:')) {
                            const event = line.substring(6).trim();
                            
                            if (event === 'complete') {
                                // Update message to not be streaming anymore
                                this.messages[messageIndex].isStreaming = false;
                                if (messageId) {
                                    this.messages[messageIndex].id = messageId;
                                }
                                await this.loadConversations();
                            }
                        } else if (line.startsWith('data:')) {
                            try {
                                const data = JSON.parse(line.substring(5));
                                
                                if (data.message_id) {
                                    messageId = data.message_id;
                                }
                                
                                if (data.chunk) {
                                    // Append chunk and render progressively
                                    this.messages[messageIndex].content += data.chunk;
                                    
                                    // Trigger re-render of markdown
                                    this.$nextTick(() => {
                                        this.scrollToBottom();
                                    });
                                }
                                
                                if (data.error) {
                                    console.error('Stream error:', data.error);
                                    this.messages[messageIndex].content = 'Erreur: ' + data.error;
                                    this.messages[messageIndex].isStreaming = false;
                                }
                            } catch (e) {
                                // Ignore JSON parse errors for incomplete lines
                            }
                        }
                    }
                }
            } catch (error) {
                console.error('Streaming error:', error);
                this.messages[messageIndex].content = 'Erreur lors de la récupération de la réponse';
                this.messages[messageIndex].isStreaming = false;
            }
        },
        
        processStreamingMarkdown: function(content) {
            if (!content) return '';
            
            // Store list counters to maintain consistency
            let orderedListCounters = {};
            let currentListDepth = 0;
            let listStack = [];
            
            // Pre-process to maintain list numbering consistency
            const lines = content.split('\n');
            let processedLines = [];
            let inOrderedList = false;
            let listCounter = 0;
            
            for (let line of lines) {
                // Check for ordered list item
                const orderedMatch = line.match(/^(\s*)(\d+)\.\s+(.*)$/);
                if (orderedMatch) {
                    const indent = orderedMatch[1].length;
                    const originalNum = orderedMatch[2];
                    const text = orderedMatch[3];
                    
                    // Track list depth changes
                    if (!inOrderedList || indent !== currentListDepth) {
                        listCounter = 1;
                        currentListDepth = indent;
                        inOrderedList = true;
                    } else {
                        listCounter++;
                    }
                    
                    // Replace with consistent numbering
                    processedLines.push(`${orderedMatch[1]}${listCounter}. ${text}`);
                } else {
                    // Not an ordered list item
                    if (inOrderedList && !line.match(/^\s+/)) {
                        // List ended
                        inOrderedList = false;
                        listCounter = 0;
                    }
                    processedLines.push(line);
                }
            }
            
            content = processedLines.join('\n');
            
            // Apply markdown processing
            let html = content
                // Headers
                .replace(/^### (.*$)/gm, '<h3 class="text-lg font-semibold mt-2 mb-1" style="color: var(--gray-900);">$1</h3>')
                .replace(/^## (.*$)/gm, '<h2 class="text-xl font-bold mt-3 mb-1" style="color: var(--gray-900);">$1</h2>')
                .replace(/^# (.*$)/gm, '<h1 class="text-2xl font-bold mt-3 mb-2" style="color: var(--gray-900);">$1</h1>')
                // Bold and italic
                .replace(/\*\*([^\*]+)\*\*/g, '<strong class="font-semibold text-gray-900">$1</strong>')
                .replace(/\*([^\*]+)\*/g, '<em>$1</em>')
                // Ordered lists
                .replace(/^(\s*)(\d+)\.\s+(.*)$/gm, '<li class="ml-6 list-decimal">$3</li>')
                // Unordered lists
                .replace(/^(\s*)-\s+(.*)$/gm, '<li class="ml-4 list-disc">$2</li>')
                // Links with target blank
                .replace(/\[([^\]]+)\]\(([^\)]+)\)\{target="_blank"\}/g, '<a href="$2" target="_blank" rel="noopener noreferrer" class="text-orange hover:text-orange-dark underline">$1</a>')
                .replace(/\[([^\]]+)\]\(([^\)]+)\)/g, '<a href="$2" class="text-orange hover:text-orange-dark underline">$1</a>')
                // Line breaks
                .replace(/\n\n/g, '</p><p class="mb-2">')
                .replace(/\n/g, '<br>');
            
            // Wrap in paragraph if not starting with block element
            if (!html.match(/^<(h[1-6]|div|ul|ol|li)/)) {
                html = '<p class="mb-2">' + html + '</p>';
            }
            
            // Wrap list items in proper list containers
            html = html.replace(/(<li class="ml-6 list-decimal">.*?<\/li>)+/gs, '<ol class="list-decimal ml-6">$&</ol>');
            html = html.replace(/(<li class="ml-4 list-disc">.*?<\/li>)+/gs, '<ul class="list-disc ml-4">$&</ul>');
            
            return html;
        }
    };
}

// Override chatInterface with streaming enhancements
function chatInterfaceWithStreaming() {
    // Get base chat interface data - first try to get it from the already defined chatInterface
    let baseData;
    
    // Try to get the original chatInterface function that should be defined later
    if (typeof window.chatInterface === 'function') {
        baseData = window.chatInterface();
    } else {
        // Fallback with complete implementation
        baseData = {
            isTyping: false,
            conversations: [],
            messages: [],
            currentConversationId: '',
            showDeleteModal: false,
            conversationToDelete: { id: null, title: '' },
            isDeleting: false,
            init() {
                console.log('Using fallback chatInterface - waiting for real implementation...');
                // Wait for the main chat script to load, then replace functions
                const tryToUpgrade = () => {
                    if (typeof window.chatInterface === 'function') {
                        console.log('Real chatInterface found! Upgrading...');
                        const realInterface = window.chatInterface();
                        Object.assign(this, realInterface);
                        // Call real init
                        if (this.init !== tryToUpgrade && typeof this.init === 'function') {
                            this.init();
                        }
                    } else {
                        setTimeout(tryToUpgrade, 100);
                    }
                };
                setTimeout(tryToUpgrade, 0);
            },
            loadConversations() { console.log('Fallback: loadConversations'); },
            createNewConversation() { console.log('Fallback: createNewConversation'); },
            switchToConversation() { console.log('Fallback: switchToConversation'); },
            deleteConversation() { console.log('Fallback: deleteConversation'); },
            sendQuickMessage() { console.log('Fallback: sendQuickMessage'); },
            sendDirectMessage() { console.log('Fallback: sendDirectMessage'); },
            copyMessage() { console.log('Fallback: copyMessage'); },
            scrollToBottom() { console.log('Fallback: scrollToBottom'); },
            processMarkdown(content) { return content || ''; }
        };
    
    const streamingEnhancements = enhanceChatWithStreaming.call(this);
    
    // Merge streaming enhancements into base data
    Object.assign(baseData, streamingEnhancements);
    
    // Override sendDirectMessage to use streaming
    const originalSendDirectMessage = baseData.sendDirectMessage;
    baseData.sendDirectMessage = async function(message, attachment) {
            if (!message.trim()) return;
            
            try {
                // Save user message first
                const formData = new FormData();
                formData.append('message', message.trim());
                formData.append('conversation_id', this.currentConversationId);
                if (attachment) {
                    formData.append('file', attachment);
                }
                
                const saveResponse = await fetch('/chat/save-user-message', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });
                
                const saveData = await saveResponse.json();
                
                if (saveData.success) {
                    // Add user message
                    const userMessage = {
                        id: saveData.message_id,
                        role: 'user',
                        content: message.trim(),
                        created_at: new Date().toISOString(),
                        attachment: saveData.attachment || null
                    };
                    
                    this.messages.push(userMessage);
                    this.scrollToBottom();
                    
                    // Update conversation ID if needed
                    if (saveData.conversation_id !== this.currentConversationId) {
                        this.currentConversationId = saveData.conversation_id;
                    }
                    
                    // Activate typing indicator
                    this.isTyping = true;
                    
                    // Use streaming for AI response - now directly accessible on 'this'
                    await this.streamAIResponse(message.trim(), saveData.vector_memory_id);
                }
            } catch (error) {
                console.error('Error sending message:', error);
            } finally {
                this.isTyping = false;
                this.scrollToBottom();
                
                if (typeof window.renderIcons === 'function') {
                    window.renderIcons();
                }
            }
        };
        
        // Override processMarkdown to handle streaming
        const originalProcessMarkdown = baseData.processMarkdown;
        baseData.processMarkdown = function(content) {
            // Check if this is a streaming message
            const isStreaming = this.messages.some(m => m.isStreaming);
            
            if (isStreaming) {
                // Use processStreamingMarkdown directly since it's now part of 'this'
                return this.processStreamingMarkdown(content);
            }
            
            // Use original processing for non-streaming messages
            return originalProcessMarkdown ? originalProcessMarkdown.call(this, content) : content;
        };
        
    return baseData;
}

// Make function available globally
window.chatInterfaceWithStreaming = chatInterfaceWithStreaming;

// Also ensure we can fallback to the regular chatInterface if needed
window.getChatInterface = function() {
    if (typeof window.chatInterfaceWithStreaming === 'function') {
        return window.chatInterfaceWithStreaming();
    } else if (typeof window.chatInterface === 'function') {
        return window.chatInterface();
    } else {
        console.error('No chat interface function available - using emergency fallback');
        return {
            isTyping: false,
            conversations: [],
            messages: [],
            currentConversationId: '',
            showDeleteModal: false,
            conversationToDelete: { id: null, title: '' },
            isDeleting: false,
            init() { console.log('Emergency fallback init'); },
            loadConversations() { console.log('Emergency fallback: loadConversations'); },
            createNewConversation() { console.log('Emergency fallback: createNewConversation'); },
            switchToConversation() { console.log('Emergency fallback: switchToConversation'); },
            deleteConversation() { console.log('Emergency fallback: deleteConversation'); },
            sendQuickMessage() { console.log('Emergency fallback: sendQuickMessage'); },
            sendDirectMessage() { console.log('Emergency fallback: sendDirectMessage'); },
            copyMessage() { console.log('Emergency fallback: copyMessage'); },
            scrollToBottom() { console.log('Emergency fallback: scrollToBottom'); },
            processMarkdown(content) { return content || ''; }
        };
    }
};
</script><?php /**PATH /Users/laminebarro/agent-O/resources/views/chat-streaming.blade.php ENDPATH**/ ?>