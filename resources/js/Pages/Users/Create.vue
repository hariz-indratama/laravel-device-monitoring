<script setup lang="ts">
import { router, Link } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { Button } from "@/Components/ui/button";
import { ArrowLeft } from "lucide-vue-next";

const creating = router.form({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    phone: "",
    role: "staff",
});

function submit() {
    creating.post("/users", {
        onSuccess: () => {
            // router akan redirect via server response
        },
    });
}
</script>

<template>
    <AppLayout title="Add User">
        <div class="max-w-lg mx-auto">
            <!-- Back -->
            <Link
                href="/users"
                class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 mb-6 transition-colors"
            >
                <ArrowLeft class="w-4 h-4" />
                Back to Users
            </Link>

            <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-sm">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="text-lg font-bold text-slate-900">Add New User</h2>
                    <p class="text-sm text-slate-400 mt-0.5">Create a team member account</p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="p-6 space-y-5">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Full Name</label>
                        <input
                            v-model="creating.name"
                            type="text"
                            required
                            placeholder="John Doe"
                            class="w-full h-10 px-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-400 transition-all"
                        />
                        <p v-if="creating.errors.name" class="mt-1 text-xs text-red-500">{{ creating.errors.name }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Email Address</label>
                        <input
                            v-model="creating.email"
                            type="email"
                            required
                            placeholder="john@example.com"
                            class="w-full h-10 px-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-400 transition-all"
                        />
                        <p v-if="creating.errors.email" class="mt-1 text-xs text-red-500">{{ creating.errors.email }}</p>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Phone <span class="text-slate-400 font-normal">(optional)</span></label>
                        <input
                            v-model="creating.phone"
                            type="text"
                            placeholder="081234567890"
                            class="w-full h-10 px-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-400 transition-all"
                        />
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Role</label>
                        <select
                            v-model="creating.role"
                            class="w-full h-10 px-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-400 transition-all cursor-pointer"
                        >
                            <option value="staff">Staff</option>
                            <option value="owner">Owner</option>
                        </select>
                        <p v-if="creating.errors.role" class="mt-1 text-xs text-red-500">{{ creating.errors.role }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                        <input
                            v-model="creating.password"
                            type="password"
                            required
                            placeholder="Min. 8 characters"
                            class="w-full h-10 px-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-400 transition-all"
                        />
                        <p v-if="creating.errors.password" class="mt-1 text-xs text-red-500">{{ creating.errors.password }}</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Confirm Password</label>
                        <input
                            v-model="creating.password_confirmation"
                            type="password"
                            required
                            placeholder="Repeat password"
                            class="w-full h-10 px-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-400 transition-all"
                        />
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-3 pt-2">
                        <Link href="/users" class="flex-1">
                            <Button type="button" variant="outline" class="w-full">Cancel</Button>
                        </Link>
                        <Button
                            type="submit"
                            class="flex-1 bg-purple-600 hover:bg-purple-700"
                            :disabled="creating.processing"
                        >
                            {{ creating.processing ? "Creating..." : "Create User" }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
