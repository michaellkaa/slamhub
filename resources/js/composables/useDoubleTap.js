/**
 * Detect double-tap / double-click for like gestures.
 * Touch path ignores scroll-like movement between taps.
 */
export function useDoubleTap(onDoubleTap, { delay = 280, maxMove = 44 } = {}) {
  let lastTapAt = 0
  let lastX = 0
  let lastY = 0

  const handleDblClick = (event, payload) => {
    onDoubleTap(payload, event)
  }

  const handleTouchEnd = (event, payload) => {
    if (!event.changedTouches || event.changedTouches.length !== 1) return

    const touch = event.changedTouches[0]
    const now = Date.now()

    if (now - lastTapAt < delay) {
      const dx = Math.abs(touch.clientX - lastX)
      const dy = Math.abs(touch.clientY - lastY)
      if (dx <= maxMove && dy <= maxMove) {
        lastTapAt = 0
        onDoubleTap(payload, event)
        return
      }
    }

    lastTapAt = now
    lastX = touch.clientX
    lastY = touch.clientY
  }

  return { handleDblClick, handleTouchEnd }
}
