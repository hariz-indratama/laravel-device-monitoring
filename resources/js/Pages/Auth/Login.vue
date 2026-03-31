<script setup lang="ts">
import { computed } from "vue";
import { useForm, usePage, Head } from "@inertiajs/vue3";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";

const page = usePage<{ intended?: string }>();

// Intended URL comes from Laravel's session (set by auth middleware on 401).
// Falls back to /dashboard if no intended URL.
const redirectTo = computed(() => page.props.intended || "/dashboard");

// Show "session expired" notice when coming back after auth redirect
const sessionExpired = computed(() =>
    page.props.intended && page.props.intended !== "/login",
);

const form = useForm({
    email: "",
    password: "",
});

function submit() {
    form.post("/login", {
        data: { redirect: redirectTo.value },
        onSuccess: () => {
            window.location.href = redirectTo.value;
        },
    });
}
</script>

<template>
    <Head title="Login — DeviceMonitor" />

    <div class="min-h-screen bg-slate-900 flex items-center justify-center p-4">
        <div class="w-full max-w-sm">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-white">
                    <span class="text-emerald-400">Device</span>Monitor
                </h1>
                <p class="text-slate-400 text-sm mt-1">IoT Monitoring System</p>
            </div>

            <!-- Session expired notice -->
            <div
                v-if="sessionExpired"
                class="mb-4 px-4 py-3 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-400 text-sm"
            >
                Sesi kamu telah berakhir. Silakan login kembali.
            </div>

            <form
                @submit.prevent="submit"
                class="bg-white rounded-xl p-6 shadow-xl space-y-4"
            >
                <div>
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="admin@example.com"
                        required
                        autocomplete="email"
                    />
                    <p
                        v-if="form.errors.email"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ form.errors.email }}
                    </p>
                </div>

                <div>
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        v-model="form.password"
                        type="password"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    />
                    <p
                        v-if="form.errors.password"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ form.errors.password }}
                    </p>
                </div>

                <Button
                    type="submit"
                    class="w-full bg-emerald-600 cursor-pointer hover:bg-emerald-700"
                    :disabled="form.processing"
                >
                    {{ form.processing ? "Logging in..." : "Login" }}
                </Button>
            </form>
        </div>
    </div>
</template>
