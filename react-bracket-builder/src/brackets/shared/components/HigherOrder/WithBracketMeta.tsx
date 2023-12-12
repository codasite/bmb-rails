import React, { useState } from 'react'
import { BracketMeta, BracketMetaContext } from '../../context/context'

export const WithBracketMeta = (Component: React.ComponentType<any>) => {
  return (props: any) => {
    const [bracketMeta, setBracketMeta] = useState<BracketMeta>({})
    return (
      <BracketMetaContext.Provider value={bracketMeta}>
        <Component
          bracketMeta={bracketMeta}
          setBracketMeta={setBracketMeta}
          {...props}
        />
      </BracketMetaContext.Provider>
    )
  }
}
