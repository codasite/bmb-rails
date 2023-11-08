import { createContext } from 'react'

interface WindowDimensionsContextState {
  height: number
  width: number
}

export const WindowDimensionsContext =
  createContext<WindowDimensionsContextState>({
    height: 0,
    width: 0,
  })
