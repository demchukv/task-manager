<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Spinner } from '@/components/ui/spinner'

const auth = useAuthStore()
const router = useRouter()

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

const handleLogin = async () => {
    error.value = ''
    loading.value = true
    try {
        await auth.login({ email: email.value, password: password.value })
        router.push('/projects')
    } catch (err: any) {
        if (err.response?.data?.errors) {
            const firstError = Object.values(err.response.data.errors)[0] as string[]
            error.value = firstError[0] || 'Validation failed'
        } else {
            error.value = err.response?.data?.message || 'Failed to login. Please check your credentials.'
        }
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <Card class="w-full max-w-md mx-auto">
        <CardHeader>
            <CardTitle class="text-2xl text-center">Sign In</CardTitle>
            <CardDescription class="text-center">
                Enter your email and password to access your account
            </CardDescription>
        </CardHeader>
        <form @submit.prevent="handleLogin">
            <CardContent class="space-y-4">
                <div class="space-y-2">
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" placeholder="m@example.com" required v-model="email" />
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Password</Label>
                    </div>
                    <Input id="password" type="password" required v-model="password" />
                </div>
                <div v-if="error" class="text-sm font-medium text-destructive text-center">
                    {{ error }}
                </div>
            </CardContent>
            <CardFooter class="flex flex-col space-y-4">
                <Button type="submit" class="w-full" :disabled="loading">
                    <span v-if="loading" class="flex items-center gap-2">
                        <Spinner /> Signing in...
                    </span>
                    <span v-else>Sign In</span>
                </Button>
                <div class="text-center text-sm text-muted-foreground">
                    Don't have an account?
                    <RouterLink to="/register" class="text-primary hover:underline font-medium">
                        Register
                    </RouterLink>
                </div>
            </CardFooter>
        </form>
    </Card>
</template>
