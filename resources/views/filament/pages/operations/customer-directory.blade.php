<x-filament-panels::page>
    <div x-data="customerDirectory()" class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-(--kn-ink)">Customer Directory</h1>
                <p class="mt-1 text-(--kn-muted)">View and manage customer information and order history.</p>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="rounded-lg bg-(--kn-surface) p-4 shadow">
                <dt class="text-sm font-medium text-(--kn-muted)">Total Customers</dt>
                <dd class="mt-1 text-2xl font-semibold text-(--kn-ink)">{{ $stats['total_customers'] }}</dd>
            </div>
            <div class="rounded-lg bg-(--kn-surface) p-4 shadow">
                <dt class="text-sm font-medium text-(--kn-muted)">Avg Lifetime Value</dt>
                <dd class="mt-1 text-2xl font-semibold text-(--kn-ink)">{{ $stats['avg_lifetime_value'] }}</dd>
            </div>
            <div class="rounded-lg bg-(--kn-surface) p-4 shadow">
                <dt class="text-sm font-medium text-(--kn-muted)">At-Risk Customers</dt>
                <dd class="mt-1 text-2xl font-semibold {{ $stats['at_risk_count'] > 0 ? 'text-(--kn-danger)' : 'text-(--kn-success)' }}">
                    {{ $stats['at_risk_count'] }}
                </dd>
            </div>
            <div class="rounded-lg bg-(--kn-surface) p-4 shadow">
                <dt class="text-sm font-medium text-(--kn-muted)">Top Customer</dt>
                <dd class="mt-1 text-lg font-semibold text-(--kn-ink)">{{ $stats['top_customer_name'] }}</dd>
                <dd class="text-sm text-(--kn-muted)">{{ $stats['top_customer_value'] }}</dd>
            </div>
        </div>

        <!-- Search -->
        <div class="rounded-lg bg-(--kn-surface) p-4 shadow">
            <div class="flex items-center space-x-4">
                <div class="flex-1">
                    <label for="search" class="sr-only">Search customers</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <x-heroicon-o-magnifying-glass class="h-5 w-5 text-(--kn-muted)" stroke-width="2" />
                        </div>
                        <input
                            wire:model.live="search"
                            id="search"
                            class="focus:ring-primary-500 focus:border-primary-500 block w-full rounded-md border border-(--kn-border) bg-(--kn-surface) py-2 pr-3 pl-10 leading-5 text-(--kn-ink) placeholder-gray-500 focus:placeholder-gray-400 focus:ring-1 focus:outline-none dark:placeholder-gray-400"
                            placeholder="Search by name, email, or phone..."
                            type="search"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer List -->
        <div class="overflow-hidden rounded-lg bg-(--kn-surface) shadow">
            <div class="min-w-full divide-y divide-(--kn-border)">
                <!-- Header -->
                <div class="bg-(--kn-surface-sunken) px-6 py-3">
                    <div class="grid grid-cols-12 gap-4 text-left text-xs font-medium tracking-wider text-(--kn-muted) uppercase">
                        <div class="col-span-3">Customer</div>
                        <div class="col-span-3">Contact</div>
                        <div class="col-span-2">Total Orders</div>
                        <div class="col-span-2">Total Spent</div>
                        <div class="col-span-2">Last Order</div>
                    </div>
                </div>

                <!-- Customer Rows -->
                <div class="divide-y divide-(--kn-border) bg-(--kn-surface)">
                    @forelse ($customers as $customer)
                        <div
                            @click="openCustomerDetails({{ $customer['id'] }})"
                            class="cursor-pointer px-6 py-4 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            <div class="grid grid-cols-12 items-center gap-4">
                                <div class="col-span-3">
                                    <div class="text-sm font-medium text-(--kn-ink)">{{ $customer['name'] }}</div>
                                </div>
                                <div class="col-span-3">
                                    <div class="text-sm text-(--kn-ink)">{{ $customer['email'] }}</div>
                                    <div class="text-sm text-(--kn-muted)">{{ $customer['phone'] }}</div>
                                </div>
                                <div class="col-span-2">
                                    <div class="text-sm text-(--kn-ink)">{{ $customer['total_orders'] }}</div>
                                </div>
                                <div class="col-span-2">
                                    <div class="text-sm text-(--kn-ink)">{{ $customer['total_spent'] }}</div>
                                </div>
                                <div class="col-span-2">
                                    <div class="text-sm text-(--kn-muted)">{{ $customer['last_order_date'] }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center">
                            <x-heroicon-o-user-group class="mx-auto h-12 w-12 text-(--kn-muted)" stroke-width="2" />
                            <h3 class="mt-2 text-sm font-medium text-(--kn-ink)">No customers found</h3>
                            <p class="mt-1 text-sm text-(--kn-muted)">
                                @if ($search)
                                    Try adjusting your search terms.
                                @else
                                    Customers will appear here once orders are placed.
                                @endif
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Slide-over panel -->
        <div
            x-show="showCustomerDetails"
            x-transition:enter="transform transition ease-in-out duration-500"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in-out duration-500"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="fixed inset-0 z-50 hidden overflow-hidden"
        >
            <div class="absolute inset-0 overflow-hidden">
                <div
                    @click="showCustomerDetails = false"
                    x-show="showCustomerDetails"
                    x-transition:enter="ease-in-out duration-500"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in-out duration-500"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="bg-opacity-75 absolute inset-0 bg-gray-500 transition-opacity"
                ></div>

                <section class="absolute inset-y-0 right-0 flex max-w-full pl-10">
                    <div class="relative w-screen max-w-2xl">
                        <div class="flex h-full flex-col overflow-y-scroll bg-(--kn-surface) shadow-xl">
                            <!-- Header -->
                            <div class="bg-(--kn-honey) px-4 py-6 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <h2
                                        class="text-lg font-medium text-(--kn-ink)"
                                        x-text="selectedCustomer?.name || 'Customer Details'"
                                    ></h2>
                                    <button
                                        @click="showCustomerDetails = false"
                                        class="text-primary-200 ml-3 h-6 w-6 transition-colors hover:text-white"
                                    >
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 px-4 py-6 sm:px-6">
                                <div x-show="loadingCustomer" class="py-8 text-center">
                                    <div class="border-primary-600 inline-block h-8 w-8 animate-spin rounded-full border-b-2"></div>
                                    <p class="mt-2 text-sm text-(--kn-muted)">Loading customer details...</p>
                                </div>

                                <div x-show="selectedCustomer && ! loadingCustomer" class="space-y-6">
                                    <!-- Customer Info -->
                                    <div>
                                        <h3 class="mb-4 text-lg font-medium text-(--kn-ink)">Contact Information</h3>
                                        <dl class="grid grid-cols-1 gap-4">
                                            <div>
                                                <dt class="text-sm font-medium text-(--kn-muted)">Email</dt>
                                                <dd
                                                    class="mt-1 text-sm text-(--kn-ink)"
                                                    x-text="selectedCustomer?.email"
                                                ></dd>
                                            </div>
                                            <div x-show="selectedCustomer?.phone">
                                                <dt class="text-sm font-medium text-(--kn-muted)">Phone</dt>
                                                <dd
                                                    class="mt-1 text-sm text-(--kn-ink)"
                                                    x-text="selectedCustomer?.phone"
                                                ></dd>
                                            </div>
                                            <div x-show="selectedCustomer?.address">
                                                <dt class="text-sm font-medium text-(--kn-muted)">Address</dt>
                                                <dd
                                                    class="mt-1 text-sm text-(--kn-ink)"
                                                    x-text="selectedCustomer?.address"
                                                ></dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Stats -->
                                    <div>
                                        <h3 class="mb-4 text-lg font-medium text-(--kn-ink)">Statistics</h3>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="rounded-lg bg-(--kn-surface-sunken) p-3">
                                                <dt class="text-sm font-medium text-(--kn-muted)">Total Orders</dt>
                                                <dd
                                                    class="mt-1 text-lg font-semibold text-(--kn-ink)"
                                                    x-text="selectedCustomer?.stats?.total_orders"
                                                ></dd>
                                            </div>
                                            <div class="rounded-lg bg-(--kn-surface-sunken) p-3">
                                                <dt class="text-sm font-medium text-(--kn-muted)">Total Spent</dt>
                                                <dd
                                                    class="mt-1 text-lg font-semibold text-(--kn-ink)"
                                                    x-text="
                                                        '$' + (selectedCustomer?.stats?.total_spent || 0).toFixed(2)
                                                    "
                                                ></dd>
                                            </div>
                                            <div class="rounded-lg bg-(--kn-surface-sunken) p-3">
                                                <dt class="text-sm font-medium text-(--kn-muted)">Avg Order Value</dt>
                                                <dd
                                                    class="mt-1 text-lg font-semibold text-(--kn-ink)"
                                                    x-text="
                                                        '$' + (selectedCustomer?.stats?.avg_order_value || 0).toFixed(2)
                                                    "
                                                ></dd>
                                            </div>
                                            <div class="rounded-lg bg-(--kn-surface-sunken) p-3">
                                                <dt class="text-sm font-medium text-(--kn-muted)">Last Order</dt>
                                                <dd
                                                    class="mt-1 text-lg font-semibold text-(--kn-ink)"
                                                    x-text="selectedCustomer?.stats?.last_order || 'Never'"
                                                ></dd>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Order History -->
                                    <div>
                                        <h3 class="mb-4 text-lg font-medium text-(--kn-ink)">Order History</h3>
                                        <div class="space-y-3">
                                            <template x-for="order in selectedCustomer?.orders || []" :key="order.id">
                                                <div class="rounded-lg border border-(--kn-border) p-4">
                                                    <div class="flex items-start justify-between">
                                                        <div>
                                                            <div
                                                                class="font-medium text-(--kn-ink)"
                                                                x-text="order.order_number"
                                                            ></div>
                                                            <div
                                                                class="text-sm text-(--kn-muted)"
                                                                x-text="order.date"
                                                            ></div>
                                                            <div
                                                                class="text-sm"
                                                                x-text="'Requested: ' + (order.delivery_date || 'N/A')"
                                                            ></div>
                                                        </div>
                                                        <div class="text-right">
                                                            <div
                                                                class="font-medium text-(--kn-ink)"
                                                                x-text="order.total"
                                                            ></div>
                                                            <div
                                                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                                                :class="{
                                                                    'bg-(--kn-warning-tint) text-(--kn-warning)':
                                                                        order.status === 'Pending',
                                                                    'bg-(--kn-info-tint) text-(--kn-info)':
                                                                        order.status === 'Confirmed',
                                                                    'bg-(--kn-warning-tint) text-(--kn-warning)':
                                                                        order.status === 'Baking',
                                                                    'bg-(--kn-success-tint) text-(--kn-success)':
                                                                        order.status === 'Ready',
                                                                    'bg-purple-100 text-purple-800':
                                                                        order.status === 'Delivered',
                                                                    'bg-(--kn-danger-tint) text-(--kn-danger)':
                                                                        order.status === 'Cancelled',
                                                                }"
                                                                x-text="order.status"
                                                            ></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                            <div
                                                x-show="
                                                    ! selectedCustomer?.orders || selectedCustomer.orders.length === 0
                                                "
                                                class="py-4 text-center text-(--kn-muted)"
                                            >
                                                No orders found
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Notes Section -->
                                    <div>
                                        <h3 class="mb-4 text-lg font-medium text-(--kn-ink)">Customer Notes</h3>

                                        <!-- Add Note Form -->
                                        <div class="mb-6 rounded-lg bg-(--kn-surface-sunken) p-4">
                                            <form
                                                wire:submit.prevent="addNote(selectedCustomer.id)"
                                                @submit="addNoteSubmitted"
                                            >
                                                {{ $this->noteForm }}
                                                <button
                                                    type="submit"
                                                    class="focus:ring-primary-500 mt-3 inline-flex items-center rounded-md border border-transparent bg-(--kn-honey) px-4 py-2 text-sm font-medium text-(--kn-on-honey) transition-colors hover:bg-(--kn-honey-hover) focus:ring-2 focus:ring-offset-2 focus:outline-none"
                                                >
                                                    Add Note
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Existing Notes -->
                                        <div class="space-y-3">
                                            <template x-for="note in selectedCustomer?.notes || []" :key="note.id">
                                                <div class="rounded-lg border border-(--kn-border) p-4">
                                                    <div class="text-sm text-(--kn-ink)" x-text="note.note"></div>
                                                    <div class="mt-2 text-xs text-(--kn-muted)">
                                                        <span x-text="'By ' + note.created_by"></span>
                                                        <span x-text="' on ' + note.created_at"></span>
                                                    </div>
                                                </div>
                                            </template>
                                            <div
                                                x-show="! selectedCustomer?.notes || selectedCustomer.notes.length === 0"
                                                class="py-4 text-center text-(--kn-muted)"
                                            >
                                                No notes added yet
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script @cspnonce>
        function customerDirectory() {
            return {
                showCustomerDetails: false,
                selectedCustomer: null,
                loadingCustomer: false,

                async openCustomerDetails(customerId) {
                    this.showCustomerDetails = true;
                    this.loadingCustomer = true;
                    this.selectedCustomer = null;

                    try {
                        this.selectedCustomer = await $wire.loadCustomerDetails(customerId);
                    } catch (error) {
                        console.error('Error fetching customer details:', error);
                    } finally {
                        this.loadingCustomer = false;
                    }
                },

                addNoteSubmitted() {
                    // Refresh customer details after adding note
                    setTimeout(() => {
                        if (this.selectedCustomer) {
                            this.openCustomerDetails(this.selectedCustomer.id);
                        }
                    }, 500);
                },
            };
        }
    </script>

    @script
        <script @cspnonce>
            // Listen for Livewire events
            $wire.on('refreshCustomerDetails', () => {
                // Trigger Alpine.js method to refresh
                if (window.Alpine && window.Alpine.evaluate) {
                    const data = window.Alpine.evaluate(
                        document.querySelector('[x-data="customerDirectory()"]'),
                        'selectedCustomer',
                    );
                    if (data && data.id) {
                        window.Alpine.evaluate(
                            document.querySelector('[x-data="customerDirectory()"]'),
                            `openCustomerDetails(${data.id})`,
                        );
                    }
                }
            });
        </script>
    @endscript
</x-filament-panels::page>
