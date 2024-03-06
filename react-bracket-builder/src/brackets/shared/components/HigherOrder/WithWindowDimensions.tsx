import { WindowDimensionsContext } from '../../context/WindowDimensionsContext'
import { useWindowDimensions } from '../../../../utils/hooks'

export const WithWindowDimensions = <P extends object>(
  Component: React.ComponentType<P>
) => {
  return (props: P) => {
    const { width, height } = useWindowDimensions()
    return (
      <WindowDimensionsContext.Provider value={{ width, height }}>
        <Component {...props} />
      </WindowDimensionsContext.Provider>
    )
  }
}
