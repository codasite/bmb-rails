import { useState, useCallback } from 'react'
import {
  SizeChangeListener,
  SizeChangeListenerContext,
} from '../../context/SizeChangeListenerContext'

export const WithSizeChangeListeners = (
  Component: React.ComponentType<any>
) => {
  return (props: any) => {
    const [sizeChangeListeners, setSizeChangeListeners] = useState<
      SizeChangeListener[]
    >([])
    const addSizeChangeListener = useCallback(
      (listener: SizeChangeListener) => {
        setSizeChangeListeners((prevListeners) => [...prevListeners, listener])
      },
      []
    )

    const removeSizeChangeListener = useCallback(
      (listener: SizeChangeListener) => {
        setSizeChangeListeners((prevListeners) =>
          prevListeners.filter((l) => l !== listener)
        )
      },
      []
    )

    return (
      <SizeChangeListenerContext.Provider
        value={{
          sizeChangeListeners,
          addSizeChangeListener,
          removeSizeChangeListener,
        }}
      >
        <Component {...props} />
      </SizeChangeListenerContext.Provider>
    )
  }
}
