<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'

const props = defineProps<{
    comment: {
        id: number
        body: string
        created_at: string
        user: {
            name: string
        }
    }
}>()

const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)
}
</script>

<template>
    <div class="flex gap-4 group">
        <div class="flex-shrink-0">
            <div
                class="h-10 w-10 rounded-full bg-primary/10 border border-primary/20 flex items-center justify-center text-primary font-bold text-xs">
                {{ getInitials(comment.user.name) }}
            </div>
        </div>
        <div class="flex-1 space-y-1">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-semibold">{{ comment.user.name }}</h4>
                <span class="text-[10px] text-muted-foreground">{{ new Date(comment.created_at).toLocaleString()
                    }}</span>
            </div>
            <Card class="bg-muted/30 border-none shadow-none">
                <CardContent class="p-3 text-sm leading-relaxed">
                    {{ comment.body }}
                </CardContent>
            </Card>
        </div>
    </div>
</template>
