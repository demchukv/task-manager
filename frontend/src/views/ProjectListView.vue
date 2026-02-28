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
        console.error('Failed to fetch projects', err)
    } finally {
        loading.value = false
    }
}

onMounted(fetchProjects)

watch([search, sort, page], fetchProjects)

const newProject = ref({ name: '', description: '' })
const showModal = ref(false)

const createProject = async () => {
    try {
        await api.post('/projects', newProject.value)
        newProject.value = { name: '', description: '' }
        showModal.value = false
        fetchProjects()
    } catch (err) {
        console.error('Failed to create project', err)
    }
}
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Projects</h1>
                <p class="text-muted-foreground text-sm">Manage and track your team's projects.</p>
            </div>
            <Button @click="showModal = true" class="shrink-0">
                <PlusIcon class="mr-2 h-4 w-4" />
                New Project
            </Button>
        </div>

        <!-- Filters -->
        <div class="flex flex-col md:flex-row gap-4">
            <div class="relative flex-1">
                <SearchIcon class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input v-model="search" type="search" placeholder="Search projects..." class="pl-8" />
            </div>
            <div class="w-full md:w-[200px]">
                <select v-model="sort"
                    class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50">
                    <option value="-created_at">Newest First</option>
                    <option value="created_at">Oldest First</option>
                    <option value="name">Name (A-Z)</option>
                    <option value="-name">Name (Z-A)</option>
                </select>
            </div>
        </div>

        <!-- Project List -->
        <div v-if="loading" class="flex flex-col items-center justify-center py-24 space-y-4">
            <Loader2Icon class="h-8 w-8 animate-spin text-primary" />
            <p class="text-sm text-muted-foreground">Loading projects...</p>
        </div>

        <div v-else-if="projects.length === 0"
            class="flex flex-col items-center justify-center py-24 border rounded-lg border-dashed bg-muted/20">
            <p class="text-muted-foreground">No projects found.</p>
            <Button variant="link" @click="search = ''" v-if="search">Clear search</Button>
            <Button variant="outline" @click="showModal = true" class="mt-4" v-else>
                Create your first project
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
                    <CardTitle>Create New Project</CardTitle>
                    <p class="text-sm text-muted-foreground">Fill in the details below to start a new project.</p>
                </CardHeader>
                <form @submit.prevent="createProject">
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Project Name</Label>
                            <Input id="name" v-model="newProject.name" placeholder="e.g. Website Redesign" required />
                        </div>
                        <div class="space-y-2">
                            <Label for="description">Description (Optional)</Label>
                            <Textarea id="description" v-model="newProject.description"
                                placeholder="Briefly describe what this project is about..." rows="4" />
                        </div>
                    </CardContent>
                    <div class="flex items-center justify-end gap-3 p-6 pt-0">
                        <Button variant="outline" type="button" @click="showModal = false">Cancel</Button>
                        <Button type="submit">Create Project</Button>
                    </div>
                </form>
            </Card>
        </div>
    </div>
</template>
