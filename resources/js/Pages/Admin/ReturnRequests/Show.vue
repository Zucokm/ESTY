<template>
    <Head :title="`Return #R-${returnRequest.id} - Admin`" />

    <div class="min-h-screen flex selection:bg-indigo-500/30 selection:text-indigo-200 bg-[#0a0a0c] text-slate-100">
        <!-- Ambient Glowing Orbs -->
        <div class="fixed top-[-20%] left-[-10%] w-[600px] h-[600px] rounded-full bg-indigo-900/10 blur-[150px] pointer-events-none"></div>
        <div class="fixed bottom-[-20%] right-[-10%] w-[600px] h-[600px] rounded-full bg-purple-900/10 blur-[150px] pointer-events-none"></div>

        <AdminSidebar active="returns" />

        <div class="flex-1 min-w-0 md:ml-64 flex flex-col min-h-screen">
            <!-- Header Bar -->
            <header class="min-h-16 py-3 bg-black/30 backdrop-blur-xl border-b border-white/[0.06] sticky top-0 z-20 px-4 sm:px-8 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3 sm:gap-4">
                    <!-- Mobile Hamburger -->
                    <button 
                        type="button"
                        @click="toggleSidebar" 
                        class="md:hidden p-2 -ml-2 text-slate-400 hover:text-white transition-colors focus:outline-none rounded-lg hover:bg-white/5"
                        title="Open Menu"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <Link 
                        :href="route('admin.returns.index')" 
                        class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-slate-400 hover:text-white transition-colors bg-white/5 hover:bg-white/10 px-3 py-1.5 rounded-xl border border-white/5"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span class="hidden sm:inline">Returns</span>
                    </Link>

                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-base sm:text-lg font-bold text-white tracking-tight">Request #R-{{ returnRequest.id }}</h1>
                            <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-md border" :class="getStatusClass(returnRequest.status)">
                                {{ returnRequest.status }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 hidden sm:block">Customer: <span class="text-slate-200 font-medium">{{ returnRequest.user.name }}</span> (Order #VR-{{ returnRequest.order.id }})</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-wrap items-center gap-2">
                    <button 
                        @click="updateStatus('reviewing')" 
                        v-if="returnRequest.status === 'pending'" 
                        class="px-3.5 py-2 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-bold text-xs shadow-lg shadow-blue-500/20 transition-all flex items-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>Mark Reviewing</span>
                    </button>

                    <button 
                        @click="updateStatus('rejected')" 
                        v-if="['pending', 'reviewing'].includes(returnRequest.status)" 
                        class="px-3.5 py-2 rounded-xl bg-rose-500/20 text-rose-300 border border-rose-500/40 hover:bg-rose-500 hover:text-white font-bold text-xs transition-all flex items-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        <span>Reject</span>
                    </button>

                    <button 
                        @click="updateStatus('approved')" 
                        v-if="['pending', 'reviewing'].includes(returnRequest.status)" 
                        class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/25 transition-all flex items-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        <span>Approve Return</span>
                    </button>

                    <button 
                        @click="updateStatus('completed')" 
                        v-if="returnRequest.status === 'approved'" 
                        class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-600/25 transition-all flex items-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>Confirm Refund & Restore Stock</span>
                    </button>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 flex flex-col">
                <!-- Mobile / Tablet Tab Switcher (Visible on < xl, hidden on desktop xl+) -->
                <div class="xl:hidden flex items-center p-1.5 bg-black/40 backdrop-blur-md border border-white/[0.08] rounded-2xl mb-6">
                    <button 
                        type="button" 
                        @click="activeTab = 'chat'"
                        :class="activeTab === 'chat' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-400 hover:text-white'"
                        class="flex-1 py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition-all duration-200"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <span>Chat Ticket</span>
                        <span v-if="returnRequest.messages && returnRequest.messages.length > 0" class="px-2 py-0.5 rounded-full text-[10px] bg-white/20 text-white font-mono">
                            {{ returnRequest.messages.length }}
                        </span>
                    </button>

                    <button 
                        type="button" 
                        @click="activeTab = 'details'"
                        :class="activeTab === 'details' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-400 hover:text-white'"
                        class="flex-1 py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition-all duration-200"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span>Order & Evidence Info</span>
                    </button>
                </div>

                <!-- Grid Layout: xl:grid-cols-12 on desktop, tab-controlled on tablet/mobile -->
                <div class="flex-1 grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
                    
                    <!-- LEFT COLUMN: Details (Order, Customer, Reason, Evidence) -->
                    <!-- Shown if activeTab === 'details' OR on screen >= xl -->
                    <div 
                        :class="[
                            'space-y-6 xl:col-span-5',
                            activeTab === 'details' ? 'block' : 'hidden xl:block'
                        ]"
                    >
                        <!-- Order Items Card -->
                        <div class="glass-card p-5 sm:p-6 border border-white/[0.08] shadow-xl">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                                    Order Items (#VR-{{ returnRequest.order.id }})
                                </h2>
                                <span class="text-xs text-slate-400 font-mono">{{ returnRequest.order.items?.length || 0 }} items</span>
                            </div>

                            <div class="space-y-3">
                                <div 
                                    v-for="item in returnRequest.order.items" 
                                    :key="item.id" 
                                    class="flex gap-3.5 items-center bg-white/[0.03] hover:bg-white/[0.05] p-3 rounded-xl border border-white/[0.04] transition-colors"
                                >
                                    <img 
                                        :src="getItemImage(item)" 
                                        class="w-14 h-14 rounded-lg object-cover bg-black/40 border border-white/10 shrink-0" 
                                        :alt="item.product.name"
                                    />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-white truncate">{{ item.product.name }}</p>
                                        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-400 mt-1">
                                            <span class="bg-white/5 px-2 py-0.5 rounded text-[11px]">Qty: {{ item.quantity }}</span>
                                            <span v-if="item.variant" class="bg-indigo-500/10 text-indigo-300 px-2 py-0.5 rounded text-[11px] border border-indigo-500/20">
                                                {{ item.variant.color }} <span v-if="item.variant.size">/ {{ item.variant.size }}</span>
                                            </span>
                                            <span class="text-slate-300 font-medium ml-auto">{{ Number(item.price).toLocaleString() }} Ks</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Info Card -->
                        <div class="glass-card p-5 sm:p-6 border border-white/[0.08] shadow-xl">
                            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                Customer Details
                            </h2>
                            <div class="flex items-center gap-3.5 bg-white/[0.03] p-3.5 rounded-xl border border-white/[0.04]">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center font-bold text-white text-sm shadow-md shadow-indigo-500/20 shrink-0">
                                    {{ returnRequest.user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-white truncate">{{ returnRequest.user.name }}</p>
                                    <p class="text-xs text-slate-400 truncate">{{ returnRequest.user.email }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Return Request Details Card -->
                        <div class="glass-card p-5 sm:p-6 border border-white/[0.08] shadow-xl space-y-4">
                            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Return Reason & Description
                            </h2>
                            <div>
                                <span class="text-[11px] font-semibold text-slate-400 block mb-1.5">Selected Reason</span>
                                <div class="text-sm font-semibold text-white bg-white/[0.04] px-4 py-2.5 rounded-xl border border-white/[0.06] flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                    {{ returnRequest.reason }}
                                </div>
                            </div>
                            <div>
                                <span class="text-[11px] font-semibold text-slate-400 block mb-1.5">Customer's Note / Issue</span>
                                <p class="text-sm text-slate-300 bg-white/[0.02] p-4 rounded-xl border border-white/[0.04] leading-relaxed break-words whitespace-pre-wrap">
                                    {{ returnRequest.description || 'No detailed note provided.' }}
                                </p>
                            </div>
                        </div>

                        <!-- Evidence Photos Card -->
                        <div v-if="returnRequest.images && returnRequest.images.length > 0" class="glass-card p-5 sm:p-6 border border-white/[0.08] shadow-xl">
                            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center justify-between">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    Evidence Photos
                                </span>
                                <span class="text-[11px] text-slate-500 lowercase">Tap to zoom</span>
                            </h2>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <div 
                                    v-for="(img, idx) in returnRequest.images" 
                                    :key="idx"
                                    @click="previewImage = img"
                                    class="relative group aspect-square rounded-xl overflow-hidden bg-black/40 border border-white/10 cursor-pointer hover:border-indigo-500/80 transition-all shadow-md"
                                >
                                    <img :src="img" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" /></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Communication Ticket (Live Chat) -->
                    <!-- Shown if activeTab === 'chat' OR on screen >= xl -->
                    <div 
                        :class="[
                            'xl:col-span-7 flex flex-col',
                            activeTab === 'chat' ? 'flex' : 'hidden xl:flex'
                        ]"
                    >
                        <div class="glass-card flex flex-col h-[calc(100vh-210px)] min-h-[520px] max-h-[850px] border border-white/[0.08] shadow-2xl rounded-2xl overflow-hidden relative">
                            <!-- Chat Header -->
                            <div class="px-5 py-4 border-b border-white/[0.06] bg-black/30 backdrop-blur-md flex items-center justify-between shrink-0">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center font-bold text-white text-sm shadow-md shadow-indigo-500/20">
                                            {{ returnRequest.user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-emerald-500 border-2 border-[#0e0e12]"></span>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-sm font-bold text-white">{{ returnRequest.user.name }}</h3>
                                            <span class="text-[10px] bg-white/10 text-slate-300 px-2 py-0.5 rounded-full font-mono">Customer</span>
                                        </div>
                                        <p class="text-[11px] text-slate-400">Direct Ticket Discussion</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button 
                                        type="button" 
                                        @click="activeTab = 'details'"
                                        class="xl:hidden px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-xs font-semibold text-slate-300 border border-white/5 transition-colors flex items-center gap-1.5"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <span>View Details</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Messages Scrollable Body -->
                            <div ref="messagesContainer" class="flex-1 min-h-0 overflow-y-auto p-4 sm:p-6 space-y-4 scroll-smooth">
                                <!-- Empty state -->
                                <div v-if="!returnRequest.messages || returnRequest.messages.length === 0" class="h-full flex flex-col items-center justify-center text-center p-8 text-slate-500">
                                    <div class="w-16 h-16 rounded-2xl bg-white/[0.03] border border-white/[0.06] flex items-center justify-center mb-3">
                                        <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-300">No messages yet</p>
                                    <p class="text-xs text-slate-500 max-w-xs mt-1">Send a message below to coordinate return details and instructions with the customer.</p>
                                </div>

                                <!-- Messages Loop -->
                                <div 
                                    v-for="msg in returnRequest.messages" 
                                    :key="msg.id" 
                                    class="flex flex-col"
                                    :class="msg.is_admin ? 'items-end' : 'items-start'"
                                >
                                    <div class="flex items-end gap-2 max-w-[88%] sm:max-w-[78%]">
                                        <div 
                                            class="rounded-2xl p-3.5 sm:p-4 text-sm shadow-md transition-all break-words whitespace-pre-wrap"
                                            :class="msg.is_admin 
                                                ? 'bg-gradient-to-r from-indigo-600 to-indigo-500 text-white rounded-br-sm shadow-indigo-600/15' 
                                                : 'bg-slate-800/90 border border-white/[0.08] text-slate-100 rounded-bl-sm shadow-black/20'"
                                        >
                                            <div class="flex items-center justify-between gap-3 text-[11px] mb-1 font-semibold" :class="msg.is_admin ? 'text-indigo-200' : 'text-slate-400'">
                                                <span>{{ msg.is_admin ? 'You (Admin)' : returnRequest.user.name }}</span>
                                                <span class="font-normal opacity-75 font-mono text-[10px]">{{ formatTime(msg.created_at) }}</span>
                                            </div>
                                            <p class="text-sm leading-relaxed">{{ msg.message }}</p>
                                            
                                            <div v-if="msg.is_admin" class="flex items-center justify-end gap-1 mt-1.5 text-[10px]">
                                                <template v-if="msg.is_read">
                                                    <span class="text-indigo-200 font-medium">Seen</span>
                                                    <svg class="w-3.5 h-3.5 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7m-1-4l4 4L22 7" /></svg>
                                                </template>
                                                <template v-else>
                                                    <span class="text-indigo-300/70">Delivered</span>
                                                    <svg class="w-3 h-3 text-indigo-300/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Response Chips -->
                            <div class="px-4 py-2 bg-black/40 border-t border-white/[0.04] overflow-x-auto flex gap-2 shrink-0 scrollbar-none">
                                <button 
                                    v-for="(quick, qIdx) in quickReplies" 
                                    :key="qIdx"
                                    type="button"
                                    @click="useQuickReply(quick)"
                                    class="shrink-0 text-[11px] bg-white/[0.04] hover:bg-white/[0.08] text-slate-300 hover:text-white px-3 py-1 rounded-full border border-white/[0.06] transition-colors"
                                >
                                    {{ quick }}
                                </button>
                            </div>

                            <!-- Chat Input Footer -->
                            <div class="p-3 sm:p-4 border-t border-white/[0.06] bg-slate-950/70 backdrop-blur-md shrink-0">
                                <form @submit.prevent="sendMessage" class="flex items-center gap-2 sm:gap-3">
                                    <input 
                                        v-model="form.message" 
                                        type="text" 
                                        class="flex-1 glass-input py-2.5 sm:py-3 px-4 text-base sm:text-sm rounded-xl focus:border-indigo-500 transition-all placeholder:text-slate-500" 
                                        placeholder="Reply to customer..." 
                                        required 
                                        :disabled="form.processing"
                                    />
                                    <button 
                                        type="submit" 
                                        :disabled="form.processing || !form.message.trim()" 
                                        class="px-5 sm:px-6 py-2.5 sm:py-3 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold rounded-xl text-sm transition-all shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 shrink-0"
                                    >
                                        <span class="hidden sm:inline">Send</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>

        <!-- Evidence Image Lightbox Modal -->
        <div 
            v-if="previewImage" 
            @click="previewImage = null" 
            class="fixed inset-0 z-50 bg-black/90 backdrop-blur-md flex items-center justify-center p-4 sm:p-8"
        >
            <div class="relative max-w-4xl max-h-[90vh] flex flex-col items-center" @click.stop>
                <img :src="previewImage" class="max-w-full max-h-[80vh] object-contain rounded-2xl border border-white/10 shadow-2xl" />
                <button 
                    @click="previewImage = null" 
                    class="mt-4 px-6 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-sm transition-colors flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    Close Preview
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, onMounted, nextTick, watch } from 'vue';
import AdminSidebar from '@/Components/AdminSidebar.vue';

const props = defineProps({
    returnRequest: Object
});

const activeTab = ref('chat');
const previewImage = ref(null);
const messagesContainer = ref(null);

const form = useForm({
    message: ''
});

const quickReplies = [
    'ကျေးဇူးပြု၍ ပစ္စည်းနှင့် ပါကင်ပုံ ရိုက်ပို့ပေးပါဗျာ။',
    'Return Request ကို စစ်ဆေးနေပါပြီခင်ဗျာ။',
    'Return လက်ခံပေးပါပြီ၊ ပစ္စည်းပို့ဆောင်ပေးပါဗျာ။',
    'ငွေပြန်လည် လွှဲပေးပြီးပါပြီခင်ဗျာ။'
];

const useQuickReply = (text) => {
    form.message = text;
};

const toggleSidebar = () => {
    window.dispatchEvent(new CustomEvent('toggle-admin-sidebar'));
};

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

onMounted(() => {
    scrollToBottom();
});

watch(() => props.returnRequest.messages, () => {
    scrollToBottom();
}, { deep: true });

const getStatusClass = (status) => {
    switch(status) {
        case 'pending': return 'bg-amber-500/10 text-amber-400 border-amber-500/20';
        case 'reviewing': return 'bg-blue-500/10 text-blue-400 border-blue-500/20';
        case 'approved': return 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20';
        case 'rejected': return 'bg-rose-500/10 text-rose-400 border-rose-500/20';
        case 'completed': return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
        default: return 'bg-slate-500/10 text-slate-400 border-slate-500/20';
    }
};

const formatTime = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const updateStatus = (status) => {
    if (confirm(`Are you sure you want to change status to ${status}?`)) {
        router.put(route('admin.returns.status', props.returnRequest.id), { status }, { preserveScroll: true });
    }
};

const sendMessage = () => {
    if (!form.message.trim()) return;
    form.post(route('admin.returns.message', props.returnRequest.id), {
        onSuccess: () => {
            form.reset();
            scrollToBottom();
        },
        preserveScroll: true
    });
};

const getItemImage = (item) => {
    if (!item.product || !item.product.images || item.product.images.length === 0) return '';
    if (item.variant && item.variant.color) {
        const color = item.variant.color.toLowerCase().trim();
        let matched = item.product.images.find(img => img.color && img.color.toLowerCase().trim() === color);
        if (!matched) {
            matched = item.product.images.find(img => img.image_path && img.image_path.toLowerCase().includes(color));
        }
        if (matched) return matched.image_path;
    }
    const primary = item.product.images.find(img => img.is_primary);
    return primary ? primary.image_path : item.product.images[0].image_path;
};
</script>
