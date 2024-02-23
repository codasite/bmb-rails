import React, { useState } from 'react'
import { PlayBuilderProps } from '../PlayBracketBuilder/types'
import { LandingPage } from './LandingPage'
import { PickableBracketPage } from './PickableBracketPage'
import { FullBracketPage } from './FullBracketPage'

export const PaginatedPlayBuilder = (props: PlayBuilderProps) => {
  const { matchTree, setMatchTree, darkMode, setDarkMode, canPlay } = props

  const [page, setPage] = useState(() => {
    if (matchTree && canPlay) {
      if (matchTree.allPicked()) {
        return 'final'
      } else if (matchTree.anyPicked()) {
        return 'bracket'
      }
    }
    return 'landing'
  })

  const onStart = () => {
    setPage('bracket')
  }

  const onFinished = () => {
    setPage('final')
  }

  let element: JSX.Element | null = null

  switch (page) {
    case 'landing':
      element = (
        <LandingPage
          matchTree={matchTree}
          onStart={onStart}
          canPlay={canPlay}
        />
      )
      break
    case 'bracket':
      element = (
        <PickableBracketPage
          matchTree={matchTree}
          setMatchTree={setMatchTree}
          onFinished={onFinished}
          darkMode={darkMode}
          setDarkMode={setDarkMode}
        />
      )
      break
    case 'final':
      element = <FullBracketPage {...props} onEditClick={onStart} />
      break
  }

  return element
}
