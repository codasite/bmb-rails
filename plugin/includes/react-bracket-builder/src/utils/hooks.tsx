import React, { useState, useEffect, createContext, useContext } from 'react'

interface WindowDimensions {
  width: number
  height: number
}

function getWindowDimensions(): WindowDimensions {
  const { innerWidth: width, innerHeight: height } = window
  return {
    width,
    height,
  }
}

export function useWindowDimensions(): WindowDimensions {
  const [windowDimensions, setWindowDimensions] = useState<WindowDimensions>(
    getWindowDimensions()
  )

  useEffect(() => {
    function handleResize(): void {
      // const { width, height } = getWindowDimensions();
      // console.log('width: ', width);
      // console.log('height: ', height);

      setWindowDimensions(getWindowDimensions())
    }

    window.addEventListener('resize', handleResize)
    return (): void => window.removeEventListener('resize', handleResize)
  }, [])

  return windowDimensions
}

// const BracketContext = createContext();

// export function BracketProvider({ children }) {
// 	const [matchTree, setMatchTree] = useState(/* initial state */);

// 	return (
// 		<BracketContext.Provider value={{ matchTree, setMatchTree }}>
// 			{children}
// 		</BracketContext.Provider>
// 	);
// }

// // Use this hook in any component to access the bracket state.
// export function useBracket() {
// 	return useContext(BracketContext);
// }

export function useDomContentLoaded() {
  const [domContentLoaded, setDomContentLoaded] = useState(
    document.readyState === 'complete' || document.readyState === 'interactive'
  )

  useEffect(() => {
    if (domContentLoaded) return

    const handleDOMContentLoaded = () => {
      setDomContentLoaded(true)
      document.removeEventListener('DOMContentLoaded', handleDOMContentLoaded)
    }

    document.addEventListener('DOMContentLoaded', handleDOMContentLoaded)

    // Cleanup function
    return () => {
      document.removeEventListener('DOMContentLoaded', handleDOMContentLoaded)
    }
  }, [domContentLoaded]) // Depend on domContentLoaded state

  return domContentLoaded
}
