<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import api from '@/api'
import { useAuthStore } from '@/stores/auth'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Label } from '@/components/ui/label'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import ProjectCard from '@/components/projects/ProjectCard.vue'
import { PlusIcon, SearchIcon, Loader2Icon } from 'lucide-vue-next'

const auth = useAuthStore()
const projects = ref<any[]>([])
const pagination = ref<any>(null)
const loading = ref(false)
const search = ref('')
const sort = ref('-created_at')
const page = ref(1)

const fetchProjects = async () => {
    loading.value = true
    try {
        const response = await api.get('/projects', {
            params: {
                q: search.value,
                sort: sort.value,
                page: page.value,
            },
        })
        projects.value = response.data.data
        pagination.value = response.data.meta
    } catch (err) {
        console.error('Не вдалося завантажити проекти', err)
    } finally {
        loading.value = false
    }
}

onMounted(fetchProjects)

watch([search, sort, page], fetchProjects)

const newProject = ref({ name: '', description: '' })
const showModal = ref(false)
const error = ref('')

watch(showModal, (val) => {
    if (val) error.value = ''
})

const createProject = async () => {
    error.value = ''
    try {
        await api.post('/projects', newProject.value)
        newProject.value = { name: '', description: '' }
        showModal.value = false
        fetchProjects()
    } catch (err: any) {
        if (err.response?.data?.errors) {
            const firstError = Object.values(err.response.data.errors)[0] as string[]
            error.value = firstError[0] || 'Помилка валідації'
        } else {
            error.value = err.response?.data?.message || 'Не вдалося створити проект'
        }
    }
}
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Проекти</h1>
                <p class="text-muted-foreground text-sm">Керуйте та відстежуйте проекти вашої команди.</p>
            </div>
            <Button @click="showModal = true" class="shrink-0">
                <PlusIcon class="mr-2 h-4 w-4" />
                Новий проект
            </Button>
        </div>

        <!-- Filters -->
        <div class="flex flex-col md:flex-row gap-4">
            <div class="relative flex-1">
                <SearchIcon class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input v-model="search" type="search" placeholder="Пошук проектів..." class="pl-8" />
            </div>
            <div class="w-full md:w-[200px]">
                <select v-model="sort"
                    class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50">
                    <option value="-created_at">Новіші першими</option>
                    <option value="created_at">Старіші першими</option>
                    <option value="name">Назва (А-Я)</option>
                    <option value="-name">Назва (Я-А)</option>
                </select>
            </div>
        </div>

        <!-- Project List -->
        <div v-if="loading" class="flex flex-col items-center justify-center py-24 space-y-4">
            <Loader2Icon class="h-8 w-8 animate-spin text-primary" />
            <p class="text-sm text-muted-foreground">Завантаження проектів...</p>
        </div>

        <div v-else-if="projects.length === 0"
            class="flex flex-col items-center justify-center py-24 border rounded-lg border-dashed bg-muted/20">
            <p class="text-muted-foreground">Проекти не знайдено.</p>
            <Button variant="link" @click="search = ''" v-if="search">Очистити пошук</Button>
            <Button variant="outline" @click="showModal = true" class="mt-4" v-else>
                Створити перший проект
            </Button>
        </div>

        <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 pb-8">
            <ProjectCard v-for="project in projects" :key="project.id" :project="project" />
        </div>

        <!-- Pagination -->
        <div v-if="pagination && pagination.last_page > 1" class="flex justify-center items-center space-x-2 pb-12">
            <Button v-for="p in pagination.last_page" :key="p" size="sm" :variant="page === p ? 'default' : 'outline'"
                @click="page = p">
                {{ p }}
            </Button>
        </div>

        <!-- Create Project Modal (Manual implementation as Dialog component isn't created yet) -->
        <div v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <Card class="w-full max-w-lg shadow-2xl animate-in fade-in zoom-in duration-200">
                <CardHeader>
                    <CardTitle>Створити новий проект</CardTitle>
                    <p class="text-sm text-muted-foreground">Заповніть деталі нижче, щоб розпочати новий проект.</p>
                </CardHeader>
                <form @submit.prevent="createProject">
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Назва проекту</Label>
                            <Input id="name" v-model="newProject.name" placeholder="Наприклад: Редизайн веб-сайту"
                                required />
                        </div>
                        <div class="space-y-2">
                            <Label for="description">Опис (необов'язково)</Label>
                            <Textarea id="description" v-model="newProject.description"
                                placeholder="Коротко опишіть, про що цей проект..." rows="4" />
                        </div>
                        <div v-if="error" class="text-sm font-medium text-destructive">
                            {{ error }}
                        </div>
                    </CardContent>
                    <div class="flex items-center justify-end gap-3 p-6 pt-0">
                        <Button variant="outline" type="button" @click="showModal = false">Скасувати</Button>
                        <Button type="submit">Створити проект</Button>
                    </div>
                </form>
            </Card>
        </div>
    </div>
</template>
