export type Variant = string | null

export type AbTestingState = {
    assignments: Record<string, Variant>
}