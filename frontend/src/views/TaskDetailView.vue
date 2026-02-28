<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import api from '@/api'
import { useAuthStore } from '@/stores/auth'
import { Button } from '@/components/ui/button'
import { Textarea } from '@/components/ui/textarea'
import { Label } from '@/components/ui/label'
import { Card, CardHeader, CardTitle, CardContent, CardDescription, CardFooter } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import CommentCard from '@/components/tasks/CommentCard.vue'
import {
    Loader2Icon,
    ChevronLeftIcon,
    CalendarIcon,
    UserIcon,
    SendIcon,
    MessageSquareIcon,
    ClockIcon
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const task = ref<any>(null)
const comments = ref<any[]>([])
const users = ref<any[]>([])
const loading = ref(true)
const newComment = ref('')
const submitting = ref(false)

const fetchTask = async () => {
    try {
        const response = await api.get(`/tasks/${route.params.id}`)
        task.value = response.data
    } catch (err) {
        console.error('Failed to fetch task', err)
    } finally {
        loading.value = false
    }
}

const fetchComments = async () => {
    try {
        const response = await api.get(`/tasks/${route.params.id}/comments`)
        comments.value = response.data
    } catch (err) {
        console.error('Failed to fetch comments', err)
    }
}

const fetchUsers = async () => {
    if (!auth.isAdmin) return
    try {
        const response = await api.get('/users')
        users.value = response.data
    } catch (err) {
        console.error('Failed to fetch users', err)
    }
}

onMounted(() => {
    fetchTask()
    fetchComments()
    fetchUsers()
})

const postComment = async () => {
    if (!newComment.value.trim()) return
    submitting.value = true
    try {
        await api.post(`/tasks/${route.params.id}/comments`, { body: newComment.value })
        newComment.value = ''
        fetchComments()
    } catch (err) {
        console.error('Failed to post comment', err)
    } finally {
        submitting.value = false
    }
}

const statusOptions = ['todo', 'in_progress', 'done']
const priorityOptions = ['low', 'medium', 'high']

const updateTask = async () => {
    try {
        await api.put(`/tasks/${task.value.id}`, {
            status: task.value.status,
            priority: task.value.priority,
            assignee_id: task.value.assignee_id,
        })
        fetchTask() // Refresh to get assignee object
    } catch (err) {
        console.error('Failed to update task', err)
    }
}

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'todo': return 'secondary'
        case 'in_progress': return 'default'
        case 'done': return 'outline'
        default: return 'secondary'
    }
}
</script>

<template>
    <div class="space-y-6">
        <div v-if="loading" class="flex flex-col items-center justify-center py-24 space-y-2">
            <Loader2Icon class="h-8 w-8 animate-spin text-primary" />
            <p class="text-sm text-muted-foreground">Loading task details...</p>
        </div>

        <div v-else-if="task" class="space-y-8 animate-in fade-in slide-in-from-bottom-2 duration-500">
            <!-- Breadcrumbs -->
            <nav class="flex items-center text-xs text-muted-foreground">
                <RouterLink :to="`/projects/${task.project_id}`"
                    class="hover:text-primary flex items-center gap-1 transition-colors">
                    <ChevronLeftIcon class="h-3 w-3" />
                    Back to Project
                </RouterLink>
                <Separator orientation="vertical" class="mx-2 h-3" />
                <span class="font-medium text-foreground truncate max-w-[200px]">{{ task.title }}</span>
            </nav>

            <!-- Task Details Card -->
            <Card class="border-2">
                <CardHeader class="pb-2">
                    <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                        <div class="space-y-1 flex-1">
                            <div class="flex items-center gap-3">
                                <Badge :variant="getStatusVariant(task.status)" class="uppercase px-2 text-[10px]">
                                    {{ task.status.replace('_', ' ') }}
                                </Badge>
                                <Badge variant="outline" class="capitalize text-[10px]">
                                    Priority: {{ task.priority }}
                                </Badge>
                            </div>
                            <CardTitle class="text-3xl font-bold pt-2 leading-tight">{{ task.title }}</CardTitle>
                        </div>

                        <div class="flex items-center gap-4 bg-muted/30 p-3 rounded-lg border w-full md:w-auto">
                            <div class="space-y-1.5 flex-1 md:w-32">
                                <Label class="text-[10px] uppercase font-bold text-muted-foreground" for="status">Quick
                                    Status</Label>
                                <select id="status" v-model="task.status" @change="updateTask"
                                    class="flex h-8 w-full rounded-md border border-input bg-background px-2 py-1 text-xs shadow-sm focus:outline-none focus:ring-1 focus:ring-ring">
                                    <option v-for="opt in statusOptions" :key="opt" :value="opt">{{ opt.replace('_',
                                        '').toUpperCase() }}</option>
                                </select>
                            </div>
                            <div class="space-y-1.5 flex-1 md:w-32">
                                <Label class="text-[10px] uppercase font-bold text-muted-foreground"
                                    for="priority">Priority</Label>
                                <select id="priority" v-model="task.priority" @change="updateTask"
                                    class="flex h-8 w-full rounded-md border border-input bg-background px-2 py-1 text-xs shadow-sm focus:outline-none focus:ring-1 focus:ring-ring">
                                    <option v-for="opt in priorityOptions" :key="opt" :value="opt">{{ opt.toUpperCase()
                                        }}</option>
                                </select>
                            </div>
                            <div v-if="auth.isAdmin" class="space-y-1.5 flex-1 md:w-48">
                                <Label class="text-[10px] uppercase font-bold text-muted-foreground"
                                    for="assignee">Assignee</Label>
                                <select id="assignee" v-model="task.assignee_id" @change="updateTask"
                                    class="flex h-8 w-full rounded-md border border-input bg-background px-2 py-1 text-xs shadow-sm focus:outline-none focus:ring-1 focus:ring-ring">
                                    <option :value="null">Unassigned</option>
                                    <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </CardHeader>

                <CardContent class="space-y-6 pt-4">
                    <div
                        class="bg-muted/10 p-4 rounded-md border-l-4 border-primary/50 italic text-sm text-foreground/80 leading-relaxed">
                        {{ task.description || 'No detailed description provided for this task.' }}
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs text-muted-foreground">
                        <div class="flex items-center gap-2">
                            <UserIcon class="h-4 w-4 text-primary" />
                            <span class="font-medium">Assigned to:</span>
                            <span class="text-foreground italic" v-if="task.assignee">{{ task.assignee.name }}</span>
                            <span class="text-muted-foreground italic" v-else>Unassigned</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <CalendarIcon class="h-4 w-4 text-primary" />
                            <span class="font-medium">Due Date:</span>
                            <span class="text-foreground italic" v-if="task.due_date">{{ new
                                Date(task.due_date).toLocaleDateString() }}</span>
                            <span class="text-muted-foreground italic" v-else>No due date</span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Comments Section -->
            <div class="space-y-6 pb-12">
                <div class="flex items-center gap-2">
                    <MessageSquareIcon class="h-5 w-5 text-primary" />
                    <h3 class="text-xl font-bold tracking-tight">Collaboration</h3>
                    <Badge variant="secondary" class="rounded-full h-5 min-w-5 flex items-center justify-center">{{
                        comments.length }}</Badge>
                </div>

                <div class="space-y-6 max-w-3xl">
                    <CommentCard v-for="comment in comments" :key="comment.id" :comment="comment" />

                    <div v-if="comments.length === 0"
                        class="flex flex-col items-center justify-center py-12 border rounded-lg border-dashed bg-muted/20 text-muted-foreground">
                        <MessageSquareIcon class="h-8 w-8 mb-2 opacity-20" />
                        <p class="text-sm">No discussion started yet.</p>
                    </div>

                    <Separator class="my-8" />

                    <!-- New Comment Form -->
                    <Card class="bg-primary/5 border-primary/10 shadow-none">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm flex items-center gap-2">
                                <div
                                    class="h-6 w-6 rounded-full bg-primary flex items-center justify-center text-[10px] text-primary-foreground font-bold uppercase">
                                    {{ auth.user?.name.charAt(0) }}
                                </div>
                                Add a Comment
                            </CardTitle>
                        </CardHeader>
                        <form @submit.prevent="postComment">
                            <CardContent>
                                <Textarea v-model="newComment" rows="3"
                                    placeholder="Share your thoughts or update the team..."
                                    class="bg-background border-primary/20 focus-visible:ring-primary/30" />
                            </CardContent>
                            <CardFooter class="flex justify-end">
                                <Button type="submit" size="sm" :disabled="submitting || !newComment.trim()">
                                    <Loader2Icon v-if="submitting" class="mr-2 h-4 w-4 animate-spin" />
                                    <SendIcon v-else class="mr-2 h-4 w-4" />
                                    Post Update
                                </Button>
                            </CardFooter>
                        </form>
                    </Card>
                </div>
            </div>
        </div>
    </div>
</template>
