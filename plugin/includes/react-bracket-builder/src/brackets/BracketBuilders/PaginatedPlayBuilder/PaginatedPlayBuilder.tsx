import React, { useState, useEffect } from 'react'
import { PlayBuilderProps } from '../PlayBracketBuilder/types'
import { PaginatedPickableBracket } from '../../shared/components/Bracket'
import { LandingPage } from './LandingPage'
import { PickableBracketPage } from './PickableBracketPage'
import { FullBracketPage } from './FullBracketPage'

export const PaginatedPlayBuilder = (props: PlayBuilderProps) => {
  const {
    matchTree,
    setMatchTree,
    darkMode,
    setDarkMode,
    handleApparelClick,
    processing,
  } = props

  const [page, setPage] = useState('landing')

  useEffect(() => {
    if (!matchTree) {
      return
    }
    if (matchTree.allPicked()) {
      console.log('all picked')
      setPage('final')
    } else if (matchTree.anyPicked()) {
      setPage('bracket')
    }
  }, [])

  const onStart = () => {
    setPage('bracket')
  }

  const onFinished = () => {
    setPage('final')
  }

  let element: JSX.Element | null = null

  switch (page) {
    case 'landing':
      element = <LandingPage matchTree={matchTree} onStart={onStart} />
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
      element = (
        <FullBracketPage
          matchTree={matchTree}
          darkMode={darkMode}
          setDarkMode={setDarkMode}
          onEditClick={onStart}
          onApparelClick={handleApparelClick}
          processing={processing}
        />
      )
      break
  }

  return element
}
