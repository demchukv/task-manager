<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { UserIcon, ClockIcon } from 'lucide-vue-next'

const props = defineProps<{
    task: {
        id: number
        title: string
        status: string
        priority: string
        due_date: string | null
        assignee?: {
            name: string
        }
    }
}>()

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'todo': return 'secondary'
        case 'in_progress': return 'default'
        case 'done': return 'outline'
        default: return 'secondary'
    }
}

const getPriorityColor = (priority: string) => {
    switch (priority) {
        case 'high': return 'text-destructive'
        case 'medium': return 'text-yellow-600 dark:text-yellow-500'
        case 'low': return 'text-green-600 dark:text-green-500'
        default: return 'text-muted-foreground'
    }
}

const formatStatus = (status: string) => {
    return status.replace('_', ' ').toUpperCase()
}
</script>

<template>
    <RouterLink :to="`/tasks/${task.id}`" class="block group">
        <Card class="group-hover:border-primary/50 transition-colors">
            <CardContent class="p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1 flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <h4 class="font-semibold text-base truncate group-hover:text-primary transition-colors">
                                {{ task.title }}
                            </h4>
                            <Badge :variant="getStatusVariant(task.status)"
                                class="shrink-0 text-[10px] px-1.5 py-0 uppercase">
                                {{ formatStatus(task.status) }}
                            </Badge>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-muted-foreground">
                            <div class="flex items-center gap-1">
                                <span class="font-medium">Пріоритет:</span>
                                <span :class="['font-bold capitalize', getPriorityColor(task.priority)]">
                                    {{ task.priority }}
                                </span>
                            </div>
                            <div v-if="task.assignee" class="flex items-center gap-1">
                                <UserIcon class="h-3 w-3" />
                                <span>{{ task.assignee.name }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="task.due_date"
                        class="flex items-center gap-1.5 text-xs text-muted-foreground bg-muted/50 px-2 py-1 rounded-md shrink-0">
                        <ClockIcon class="h-3.5 w-3.5" />
                        <span>Кінцевий термін: {{ new Date(task.due_date).toLocaleDateString() }}</span>
                    </div>
                </div>
            </CardContent>
        </Card>
    </RouterLink>
</template>
