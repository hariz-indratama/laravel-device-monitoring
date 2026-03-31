<script setup lang="ts">
import { router, Link, usePage } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { Button } from "@/Components/ui/button";
import { ArrowLeft } from "lucide-vue-next";

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const page = usePage() as any;
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const editUser = page.props.editUser as any;

const form = router.form({
    name: editUser?.name || "",
    email: editUser?.email || "",
    password: "",
    password_confirmation: "",
    phone: editUser?.phone || "",
    role: editUser?.role || "staff",
});

function submit() {
    form.put(`/users/${editUser.id}`, {
        onSuccess: () => {
            // redirect via server response
        },
    });
}
</script>

<template>
    <AppLayout title="Edit User">
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
                    <h2 class="text-lg font-bold text-slate-900">Edit User</h2>
                    <p class="text-sm text-slate-400 mt-0.5">Update user information</p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="p-6 space-y-5">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Full Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="John Doe"
                            class="w-full h-10 px-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-400 transition-all"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Email Address</label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="john@example.com"
                            class="w-full h-10 px-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-400 transition-all"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Phone <span class="text-slate-400 font-normal">(optional)</span></label>
                        <input
                            v-model="form.phone"
                            type="text"
                            placeholder="081234567890"
                            class="w-full h-10 px-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-400 transition-all"
                        />
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Role</label>
                        <select
                            v-model="form.role"
                            class="w-full h-10 px-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-400 transition-all cursor-pointer"
                        >
                            <option value="staff">Staff</option>
                            <option value="owner">Owner</option>
                        </select>
                        <p v-if="form.errors.role" class="mt-1 text-xs text-red-500">{{ form.errors.role }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Password <span class="text-slate-400 font-normal">(leave blank to keep current)</span>
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            placeholder="New password"
                            class="w-full h-10 px-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-400 transition-all"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
                    </div>

                    <!-- Confirm Password -->
                    <div v-if="form.password">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Confirm New Password</label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            placeholder="Repeat new password"
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
                            :disabled="form.processing"
                        >
                            {{ form.processing ? "Saving..." : "Save Changes" }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
