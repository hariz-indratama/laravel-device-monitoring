<script setup lang="ts">
import { ref } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/Components/ui/tabs";
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
    CardFooter,
} from "@/Components/ui/card";
import { Label } from "@/Components/ui/label";
import { Button } from "@/Components/ui/button";
import { Switch } from "@/Components/ui/switch";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { Separator } from "@/Components/ui/separator";
import { BellRing, Monitor, Fingerprint } from "lucide-vue-next";

const props = defineProps<{
    settings: {
        notifications: {
            email_alerts: boolean;
            telegram_alerts: boolean;
            weekly_report: boolean;
        };
        appearance: {
            theme: string;
        };
        locale: string;
    };
}>();

// Reactive dummy state connected to props for UI simulation
const notifications = ref(props.settings.notifications);
const appearance = ref(props.settings.appearance);
const locale = ref(props.settings.locale);
const isLoading = ref(false);

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const page = usePage() as any;

const saveSettings = () => {
    isLoading.value = true;
    setTimeout(() => {
        isLoading.value = false;
        page.props.flash.success = "Settings updated successfully.";
    }, 800);
};
</script>

<template>
    <Head title="Settings" />

    <AppLayout title="Settings">
        <div class="max-w-5xl">
            <Tabs
                defaultValue="general"
                class="flex flex-col md:flex-row gap-6 lg:gap-10"
            >
                <!-- Sidebar Tabs Navigation -->
                <TabsList
                    class="flex md:flex-col h-auto bg-transparent border-0 justify-start items-start gap-1 p-0 md:w-56 overflow-x-auto"
                >
                    <TabsTrigger
                        value="general"
                        class="w-full justify-start rounded-lg data-[state=active]:bg-emerald-50 data-[state=active]:text-emerald-700 data-[state=active]:shadow-none hover:bg-slate-100 px-4 py-2.5 transition-colors"
                    >
                        General
                    </TabsTrigger>
                    <TabsTrigger
                        value="notifications"
                        class="w-full justify-start rounded-lg data-[state=active]:bg-emerald-50 data-[state=active]:text-emerald-700 data-[state=active]:shadow-none hover:bg-slate-100 px-4 py-2.5 transition-colors"
                    >
                        Notifications
                    </TabsTrigger>
                    <TabsTrigger
                        value="appearance"
                        class="w-full justify-start rounded-lg data-[state=active]:bg-emerald-50 data-[state=active]:text-emerald-700 data-[state=active]:shadow-none hover:bg-slate-100 px-4 py-2.5 transition-colors"
                    >
                        Appearance
                    </TabsTrigger>
                </TabsList>

                <!-- Tab Contents -->
                <div class="flex-1">
                    <!-- General Settings -->
                    <TabsContent
                        value="general"
                        class="mt-0 ring-offset-background outline-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                    >
                        <Card
                            class="border-slate-200/60 shadow-sm animate-in fade-in zoom-in-95 duration-200"
                        >
                            <CardHeader>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="p-2.5 bg-slate-100 rounded-xl text-slate-600"
                                    >
                                        <Fingerprint class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <CardTitle class="text-xl"
                                            >General Settings</CardTitle
                                        >
                                        <CardDescription>
                                            Manage your core application
                                            preferences like language and
                                            region.
                                        </CardDescription>
                                    </div>
                                </div>
                            </CardHeader>
                            <Separator class="bg-slate-100" />
                            <CardContent class="pt-6 space-y-6">
                                <div
                                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                                >
                                    <div class="space-y-0.5">
                                        <Label
                                            class="text-base text-slate-800 font-semibold"
                                            >Language Preference</Label
                                        >
                                        <p class="text-sm text-slate-500">
                                            Choose the default language for the
                                            interface.
                                        </p>
                                    </div>
                                    <Select v-model="locale">
                                        <SelectTrigger class="w-[180px]">
                                            <SelectValue
                                                placeholder="Select Language"
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="en"
                                                >English (US)</SelectItem
                                            >
                                            <SelectItem value="id"
                                                >Bahasa Indonesia</SelectItem
                                            >
                                        </SelectContent>
                                    </Select>
                                </div>
                            </CardContent>
                            <CardFooter
                                class="justify-end border-t border-slate-100 bg-slate-50/50 py-4 px-6 rounded-b-xl"
                            >
                                <Button
                                    @click="saveSettings"
                                    :disabled="isLoading"
                                    class="bg-emerald-600 hover:bg-emerald-700"
                                >
                                    {{
                                        isLoading
                                            ? "Saving..."
                                            : "Save Settings"
                                    }}
                                </Button>
                            </CardFooter>
                        </Card>
                    </TabsContent>

                    <!-- Notifications Settings -->
                    <TabsContent
                        value="notifications"
                        class="mt-0 ring-offset-background outline-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                    >
                        <Card
                            class="border-slate-200/60 shadow-sm animate-in fade-in zoom-in-95 duration-200"
                        >
                            <CardHeader>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="p-2.5 bg-sky-100 rounded-xl text-sky-600"
                                    >
                                        <BellRing class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <CardTitle class="text-xl"
                                            >Notification Preferences</CardTitle
                                        >
                                        <CardDescription>
                                            Choose how and when you want to be
                                            notified about device alerts.
                                        </CardDescription>
                                    </div>
                                </div>
                            </CardHeader>
                            <Separator class="bg-slate-100" />
                            <CardContent class="pt-6 space-y-6">
                                <div
                                    class="flex flex-row items-center justify-between rounded-lg border p-4 hover:bg-slate-50 transition-colors"
                                >
                                    <div class="space-y-0.5 max-w-[80%]">
                                        <Label
                                            class="text-base text-slate-800 font-semibold cursor-pointer"
                                            for="email-alerts"
                                            >Email Alerts</Label
                                        >
                                        <p class="text-sm text-slate-500">
                                            Receive critical device offline
                                            alerts via your registered email
                                            address.
                                        </p>
                                    </div>
                                    <Switch
                                        id="email-alerts"
                                        v-model="notifications.email_alerts"
                                    />
                                </div>

                                <div
                                    class="flex flex-row items-center justify-between rounded-lg border p-4 hover:bg-slate-50 transition-colors"
                                >
                                    <div class="space-y-0.5 max-w-[80%]">
                                        <Label
                                            class="text-base text-slate-800 font-semibold cursor-pointer"
                                            for="telegram-alerts"
                                            >Telegram Integration</Label
                                        >
                                        <p class="text-sm text-slate-500">
                                            Receive instant push notifications
                                            via the company Telegram Bot.
                                        </p>
                                    </div>
                                    <Switch
                                        id="telegram-alerts"
                                        v-model="notifications.telegram_alerts"
                                    />
                                </div>

                                <div
                                    class="flex flex-row items-center justify-between rounded-lg border p-4 hover:bg-slate-50 transition-colors"
                                >
                                    <div class="space-y-0.5 max-w-[80%]">
                                        <Label
                                            class="text-base text-slate-800 font-semibold cursor-pointer"
                                            for="weekly-report"
                                            >Weekly Summary</Label
                                        >
                                        <p class="text-sm text-slate-500">
                                            Receive a weekly PDF report of
                                            device performance and uptime stats.
                                        </p>
                                    </div>
                                    <Switch
                                        id="weekly-report"
                                        v-model="notifications.weekly_report"
                                    />
                                </div>
                            </CardContent>
                            <CardFooter
                                class="justify-end border-t border-slate-100 bg-slate-50/50 py-4 px-6 rounded-b-xl"
                            >
                                <Button
                                    @click="saveSettings"
                                    :disabled="isLoading"
                                    class="bg-emerald-600 hover:bg-emerald-700"
                                >
                                    {{
                                        isLoading
                                            ? "Saving..."
                                            : "Save Settings"
                                    }}
                                </Button>
                            </CardFooter>
                        </Card>
                    </TabsContent>

                    <!-- Appearance Settings -->
                    <TabsContent
                        value="appearance"
                        class="mt-0 ring-offset-background outline-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                    >
                        <Card
                            class="border-slate-200/60 shadow-sm animate-in fade-in zoom-in-95 duration-200"
                        >
                            <CardHeader>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="p-2.5 bg-indigo-100 rounded-xl text-indigo-600"
                                    >
                                        <Monitor class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <CardTitle class="text-xl"
                                            >Appearance</CardTitle
                                        >
                                        <CardDescription>
                                            Customize how the application looks
                                            on your device.
                                        </CardDescription>
                                    </div>
                                </div>
                            </CardHeader>
                            <Separator class="bg-slate-100" />
                            <CardContent class="pt-6">
                                <div class="flex flex-col space-y-4">
                                    <div class="space-y-1">
                                        <Label
                                            class="text-base text-slate-800 font-semibold"
                                            >Theme</Label
                                        >
                                        <p class="text-sm text-slate-500">
                                            Select your preferred color theme.
                                            Dark mode is coming soon.
                                        </p>
                                    </div>
                                    <div
                                        class="grid grid-cols-1 sm:grid-cols-3 gap-4 custom-radio-cards pt-2"
                                    >
                                        <!-- Light Theme -->
                                        <label class="cursor-pointer">
                                            <input
                                                type="radio"
                                                value="light"
                                                v-model="appearance.theme"
                                                class="peer sr-only"
                                            />
                                            <div
                                                class="rounded-xl border-2 border-slate-200 p-1 hover:bg-slate-50 peer-checked:border-emerald-500 peer-checked:bg-emerald-50/50 transition-all"
                                            >
                                                <div
                                                    class="h-24 rounded-lg bg-slate-100 border border-slate-200 shadow-sm flex items-center justify-center overflow-hidden"
                                                >
                                                    <div
                                                        class="w-full h-full p-2 bg-white flex flex-col gap-2"
                                                    >
                                                        <div
                                                            class="h-3 w-1/3 bg-slate-200 rounded"
                                                        ></div>
                                                        <div
                                                            class="h-full w-full bg-slate-100 rounded"
                                                        ></div>
                                                    </div>
                                                </div>
                                                <div
                                                    class="p-2 text-center text-sm font-medium text-slate-700"
                                                >
                                                    Light
                                                </div>
                                            </div>
                                        </label>

                                        <!-- Dark Theme -->
                                        <label
                                            class="cursor-pointer opacity-50"
                                        >
                                            <input
                                                type="radio"
                                                value="dark"
                                                disabled
                                                class="peer sr-only"
                                            />
                                            <div
                                                class="rounded-xl border-2 border-slate-200 p-1 bg-slate-50 transition-all relative overflow-hidden"
                                            >
                                                <div
                                                    class="absolute inset-0 z-10 bg-white/40 flex items-center justify-center"
                                                >
                                                    <span
                                                        class="bg-slate-800 text-white text-[10px] uppercase font-bold px-2 py-0.5 rounded-full shadow-sm"
                                                        >Translating UI</span
                                                    >
                                                </div>
                                                <div
                                                    class="h-24 rounded-lg bg-slate-900 border border-slate-700 shadow-sm flex items-center justify-center overflow-hidden"
                                                >
                                                    <div
                                                        class="w-full h-full p-2 bg-slate-950 flex flex-col gap-2"
                                                    >
                                                        <div
                                                            class="h-3 w-1/3 bg-slate-800 rounded"
                                                        ></div>
                                                        <div
                                                            class="h-full w-full bg-slate-900 rounded"
                                                        ></div>
                                                    </div>
                                                </div>
                                                <div
                                                    class="p-2 text-center text-sm font-medium text-slate-500"
                                                >
                                                    Dark (WIP)
                                                </div>
                                            </div>
                                        </label>

                                        <!-- System Theme -->
                                        <label class="cursor-pointer">
                                            <input
                                                type="radio"
                                                value="system"
                                                v-model="appearance.theme"
                                                class="peer sr-only"
                                            />
                                            <div
                                                class="rounded-xl border-2 border-slate-200 p-1 hover:bg-slate-50 peer-checked:border-emerald-500 peer-checked:bg-emerald-50/50 transition-all"
                                            >
                                                <div
                                                    class="h-24 rounded-lg border border-slate-200 shadow-sm flex overflow-hidden"
                                                >
                                                    <div
                                                        class="w-1/2 h-full p-2 bg-white flex flex-col gap-2 border-r border-slate-200"
                                                    >
                                                        <div
                                                            class="h-3 w-2/3 bg-slate-200 rounded"
                                                        ></div>
                                                        <div
                                                            class="h-full w-full bg-slate-100 rounded"
                                                        ></div>
                                                    </div>
                                                    <div
                                                        class="w-1/2 h-full p-2 bg-slate-950 flex flex-col gap-2"
                                                    >
                                                        <div
                                                            class="h-3 w-2/3 bg-slate-800 rounded"
                                                        ></div>
                                                        <div
                                                            class="h-full w-full bg-slate-900 rounded"
                                                        ></div>
                                                    </div>
                                                </div>
                                                <div
                                                    class="p-2 text-center text-sm font-medium text-slate-700"
                                                >
                                                    System
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </CardContent>
                            <CardFooter
                                class="justify-end border-t border-slate-100 bg-slate-50/50 py-4 px-6 rounded-b-xl"
                            >
                                <Button
                                    @click="saveSettings"
                                    :disabled="isLoading"
                                    class="bg-emerald-600 hover:bg-emerald-700"
                                >
                                    {{
                                        isLoading
                                            ? "Saving..."
                                            : "Save Settings"
                                    }}
                                </Button>
                            </CardFooter>
                        </Card>
                    </TabsContent>
                </div>
            </Tabs>
        </div>
    </AppLayout>
</template>
