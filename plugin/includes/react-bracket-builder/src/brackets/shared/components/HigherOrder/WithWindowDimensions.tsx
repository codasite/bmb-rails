import { WindowDimensionsContext } from '../../context/WindowDimensionsContext'
import { useWindowDimensions } from '../../../../utils/hooks'

export const WithWindowDimensions = (Component: any) => {
  return (props: any) => {
    const { width, height } = useWindowDimensions()
    return (
      <WindowDimensionsContext.Provider value={{ width, height }}>
        <Component {...props} />
      </WindowDimensionsContext.Provider>
    )
  }
}
