<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'

const auth = useAuthStore()
const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const password_confirmation = ref('')
const error = ref('')
const loading = ref(false)

const handleRegister = async () => {
    error.value = ''
    if (password.value !== password_confirmation.value) {
        error.value = 'Passwords do not match'
        return
    }

    loading.value = true
    try {
        await auth.register({
            name: name.value,
            email: email.value,
            password: password.value,
            password_confirmation: password_confirmation.value
        })
        router.push('/projects')
    } catch (err: any) {
        error.value = err.response?.data?.message || 'Failed to register. Please check your information.'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <Card class="w-full max-w-md mx-auto">
        <CardHeader>
            <CardTitle class="text-2xl text-center">Create Account</CardTitle>
            <CardDescription class="text-center">
                Enter your details to create a new account
            </CardDescription>
        </CardHeader>
        <form @submit.prevent="handleRegister">
            <CardContent class="space-y-4">
                <div class="space-y-2">
                    <Label for="name">Full Name</Label>
                    <Input id="name" type="text" placeholder="John Doe" required v-model="name" />
                </div>
                <div class="space-y-2">
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" placeholder="m@example.com" required v-model="email" />
                </div>
                <div class="space-y-2">
                    <Label for="password">Password</Label>
                    <Input id="password" type="password" required v-model="password" />
                </div>
                <div class="space-y-2">
                    <Label for="password_confirmation">Confirm Password</Label>
                    <Input id="password_confirmation" type="password" required v-model="password_confirmation" />
                </div>
                <div v-if="error" class="text-sm font-medium text-destructive text-center">
                    {{ error }}
                </div>
            </CardContent>
            <CardFooter class="flex flex-col space-y-4">
                <Button type="submit" class="w-full" :disabled="loading">
                    <span v-if="loading">Creating account...</span>
                    <span v-else>Register</span>
                </Button>
                <div class="text-center text-sm text-muted-foreground">
                    Already have an account?
                    <RouterLink to="/login" class="text-primary hover:underline font-medium">
                        Sign In
                    </RouterLink>
                </div>
            </CardFooter>
        </form>
    </Card>
</template>
