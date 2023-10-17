import { WildcardPlacement } from '../WildcardPlacement'
import { WildcardRange } from '../WildcardRange'

export const getWildcardRange = (
  start: number,
  end: number,
  count: number,
  placement: WildcardPlacement
): WildcardRange[] => {
  switch (placement) {
    case WildcardPlacement.Top:
      return [{ min: start, max: start + count }]
    case WildcardPlacement.Bottom:
      return [{ min: end - count, max: end }]
    case WildcardPlacement.Center:
      const offset = (end - start - count) / 2
      return [{ min: start + offset, max: end - offset }]
    case WildcardPlacement.Split:
      return [
        { min: start, max: start + count / 2 },
        { min: end - count / 2, max: end },
      ]
  }
}
