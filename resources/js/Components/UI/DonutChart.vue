<script setup lang="ts">
import { computed } from 'vue';

interface Segment { label: string; value: number; color: string }

const props = withDefaults(defineProps<{
    segments: Segment[];
    size?: number;
    thickness?: number;
}>(), { size: 170, thickness: 24 });

const total = computed(() => props.segments.reduce((s, x) => s + x.value, 0));
const radius = computed(() => (props.size - props.thickness) / 2);
const circ = computed(() => 2 * Math.PI * radius.value);

const arcs = computed(() => {
    let acc = 0;
    return props.segments
        .filter(s => s.value > 0)
        .map(s => {
            const frac = total.value ? s.value / total.value : 0;
            const dash = frac * circ.value;
            const arc = { color: s.color, dash, gap: circ.value - dash, offset: -acc };
            acc += dash;
            return arc;
        });
});
</script>

<template>
    <div class="flex items-center gap-5">
        <div class="relative shrink-0" :style="{ width: size + 'px', height: size + 'px' }">
            <svg :width="size" :height="size" :viewBox="`0 0 ${size} ${size}`" class="-rotate-90">
                <circle :cx="size / 2" :cy="size / 2" :r="radius" fill="none" stroke="#f1f5f9" :stroke-width="thickness" />
                <circle v-for="(a, i) in arcs" :key="i" :cx="size / 2" :cy="size / 2" :r="radius" fill="none"
                    :stroke="a.color" :stroke-width="thickness"
                    :stroke-dasharray="`${a.dash} ${a.gap}`" :stroke-dashoffset="a.offset" />
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-2xl font-bold text-slate-900">{{ total }}</span>
                <span class="text-[11px] text-slate-400">жами</span>
            </div>
        </div>

        <div class="flex-1 space-y-2">
            <div v-for="(s, i) in segments" :key="i" class="flex items-center gap-2 text-sm">
                <span class="h-3 w-3 shrink-0 rounded-sm" :style="{ backgroundColor: s.color }"></span>
                <span class="text-slate-600">{{ s.label }}</span>
                <span class="ml-auto font-semibold text-slate-900">{{ s.value }}</span>
                <span class="w-10 text-right text-xs text-slate-400">
                    {{ total ? Math.round((s.value / total) * 100) : 0 }}%
                </span>
            </div>
        </div>
    </div>
</template>
