import { createContext } from 'react'
export type SizeChangeListener = (height: number, width: number) => void

export interface SizeChangeListenerContextState {
  sizeChangeListeners?: (SizeChangeListener | null)[]
  addSizeChangeListener?: (listener: SizeChangeListener) => void
  removeSizeChangeListener?: (listener: SizeChangeListener) => void
}

export const SizeChangeListenerContext =
  createContext<SizeChangeListenerContextState>({})
