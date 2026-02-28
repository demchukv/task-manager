<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { CalendarIcon, UserIcon, ListTodoIcon } from 'lucide-vue-next'

defineProps<{
    project: {
        id: number
        name: string
        description: string | null
        created_at: string
        tasks_count?: number
        owner?: {
            name: string
        }
    }
}>()
</script>

<template>
    <Card class="hover:shadow-md transition-shadow">
        <CardHeader class="pb-3">
            <div class="flex justify-between items-start gap-2">
                <CardTitle class="text-xl truncate">
                    <RouterLink :to="`/projects/${project.id}`" class="hover:text-gray-600 transition-colors">
                        {{ project.name }}
                    </RouterLink>
                </CardTitle>
            </div>
            <CardDescription class="line-clamp-2 min-h-[40px]">
                {{ project.description || 'Немає опису.' }}
            </CardDescription>
        </CardHeader>
        <CardContent>
            <div class="grid grid-cols-2 gap-2">
                <div class="flex items-center text-[10px] text-muted-foreground">
                    <CalendarIcon class="mr-1 h-3 w-3" />
                    {{ new Date(project.created_at).toLocaleDateString() }}
                </div>
                <div class="flex items-center text-[10px] font-medium justify-end">
                    <ListTodoIcon class="mr-1 h-3 w-3 text-primary" />
                    <span>{{ project.tasks_count || 0 }} завдань</span>
                </div>
                <div class="flex items-center text-[10px] font-medium col-span-2">
                    <UserIcon class="mr-1 h-3 w-3 text-muted-foreground" />
                    <span class="text-muted-foreground mr-1">Власник:</span>
                    <span class="font-semibold">{{ project.owner?.name || 'Невідомо' }}</span>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
