<script setup lang="ts">
import { ref } from 'vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/Components/ui/card'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Button } from '@/Components/ui/button'
import { Separator } from '@/Components/ui/separator'
import { ShieldAlert, User, Mail, Phone, KeyRound } from 'lucide-vue-next'

const props = defineProps<{
    user: {
        id: number
        name: string
        email: string
        phone: string | null
        role: string
    }
}>()

// Profile info form
const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone ?? '',
})

// Password change form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const updateProfile = () => {
    profileForm.patch('/profile', {
        preserveScroll: true,
    })
}

const updatePassword = () => {
    passwordForm.patch('/profile/password', {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    })
}
</script>

<template>
    <Head title="My Profile" />

    <AppLayout title="My Profile">
        <div class="max-w-4xl space-y-8">
            <!-- Profile Information Card -->
            <Card class="border-slate-200/60 shadow-sm">
                <CardHeader>
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-emerald-100 rounded-xl text-emerald-600">
                            <User class="w-5 h-5" />
                        </div>
                        <div>
                            <CardTitle class="text-xl">Profile Information</CardTitle>
                            <CardDescription>
                                Update your account's profile information and email address.
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <Separator class="bg-slate-100" />
                
                <form @submit.prevent="updateProfile">
                    <CardContent class="pt-6 space-y-5">
                        <div class="grid gap-2">
                            <Label for="name" class="text-slate-600">Full Name</Label>
                            <div class="relative">
                                <User class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                <Input 
                                    id="name" 
                                    v-model="profileForm.name" 
                                    class="pl-9" 
                                    placeholder="Jane Doe" 
                                    required 
                                />
                            </div>
                            <p v-if="profileForm.errors.name" class="text-[13px] text-red-500 font-medium">
                                {{ profileForm.errors.name }}
                            </p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="email" class="text-slate-600">Email Address</Label>
                            <div class="relative">
                                <Mail class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                <Input 
                                    id="email" 
                                    type="email" 
                                    v-model="profileForm.email" 
                                    class="pl-9" 
                                    placeholder="jane@example.com" 
                                    required 
                                />
                            </div>
                            <p v-if="profileForm.errors.email" class="text-[13px] text-red-500 font-medium">
                                {{ profileForm.errors.email }}
                            </p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="phone" class="text-slate-600">Phone Number (Optional)</Label>
                            <div class="relative">
                                <Phone class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                <Input 
                                    id="phone" 
                                    type="tel" 
                                    v-model="profileForm.phone" 
                                    class="pl-9" 
                                    placeholder="+62 812..." 
                                />
                            </div>
                            <p v-if="profileForm.errors.phone" class="text-[13px] text-red-500 font-medium">
                                {{ profileForm.errors.phone }}
                            </p>
                        </div>
                        
                        <div class="grid gap-2 pt-2">
                            <Label class="text-slate-600">Account Role</Label>
                            <div class="flex items-center gap-2 text-sm text-slate-700 bg-slate-50 border border-slate-200 rounded-lg p-3">
                                <ShieldAlert v-if="user.role === 'owner'" class="w-4 h-4 text-purple-500" />
                                <User v-else class="w-4 h-4 text-slate-500" />
                                <span class="capitalize font-medium">{{ user.role }}</span>
                                <span class="text-slate-500 ml-1">(Cannot be changed)</span>
                            </div>
                        </div>
                    </CardContent>
                    <Separator class="bg-slate-100" />
                    <CardFooter class="pt-6 justify-end gap-3 bg-slate-50/50 rounded-b-xl border-t border-slate-100/50">
                        <Button 
                            type="submit" 
                            class="bg-emerald-600 hover:bg-emerald-700 shadow-sm shadow-emerald-200" 
                            :disabled="profileForm.processing"
                        >
                            {{ profileForm.processing ? 'Saving...' : 'Save Changes' }}
                        </Button>
                    </CardFooter>
                </form>
            </Card>

            <!-- Update Password Card -->
            <Card class="border-slate-200/60 shadow-sm">
                <CardHeader>
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-slate-100 rounded-xl text-slate-600">
                            <KeyRound class="w-5 h-5" />
                        </div>
                        <div>
                            <CardTitle class="text-xl">Update Password</CardTitle>
                            <CardDescription>
                                Ensure your account is using a long, random password to stay secure.
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <Separator class="bg-slate-100" />
                
                <form @submit.prevent="updatePassword">
                    <CardContent class="pt-6 space-y-5">
                        <div class="grid gap-2 max-w-xl">
                            <Label for="current_password" class="text-slate-600">Current Password</Label>
                            <Input 
                                id="current_password" 
                                type="password" 
                                v-model="passwordForm.current_password" 
                                required 
                            />
                            <p v-if="passwordForm.errors.current_password" class="text-[13px] text-red-500 font-medium">
                                {{ passwordForm.errors.current_password }}
                            </p>
                        </div>

                        <div class="grid gap-2 max-w-xl">
                            <Label for="password" class="text-slate-600">New Password</Label>
                            <Input 
                                id="password" 
                                type="password" 
                                v-model="passwordForm.password" 
                                required 
                            />
                            <p v-if="passwordForm.errors.password" class="text-[13px] text-red-500 font-medium">
                                {{ passwordForm.errors.password }}
                            </p>
                        </div>

                        <div class="grid gap-2 max-w-xl">
                            <Label for="password_confirmation" class="text-slate-600">Confirm Password</Label>
                            <Input 
                                id="password_confirmation" 
                                type="password" 
                                v-model="passwordForm.password_confirmation" 
                                required 
                            />
                            <p v-if="passwordForm.errors.password_confirmation" class="text-[13px] text-red-500 font-medium">
                                {{ passwordForm.errors.password_confirmation }}
                            </p>
                        </div>
                    </CardContent>
                    <Separator class="bg-slate-100" />
                    <CardFooter class="pt-6 justify-end gap-3 bg-slate-50/50 rounded-b-xl border-t border-slate-100/50">
                        <Button 
                            type="submit" 
                            variant="secondary"
                            class="bg-slate-900 text-white hover:bg-slate-800" 
                            :disabled="passwordForm.processing"
                        >
                            {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
