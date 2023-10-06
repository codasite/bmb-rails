import React, { useEffect, useState } from 'react'
import { ThemeSelector } from '../../shared/components'
import { MatchTree } from '../../shared/models/MatchTree'
import { PickableBracket } from '../../shared/components/Bracket'
import { ActionButton } from '../../shared/components/ActionButtons'
import {
  WithDarkMode,
  WithMatchTree,
  WithBracketMeta,
  WithProvider,
} from '../../shared/components/HigherOrder'
//@ts-ignore
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
//@ts-ignore
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { BracketMeta } from '../../shared/context'
import { bracketApi } from '../../shared/api/bracketApi'
import { BustPlayBuilder } from './BustPlayBuilder'

interface BustPlayPageProps {
  bracketMeta: BracketMeta
  setBracketMeta: (bracketMeta: BracketMeta) => void
  matchTree: MatchTree
  setMatchTree: (matchTree: MatchTree) => void
  bracketPlay: any
  apparelUrl: string
  darkMode: boolean
  setDarkMode: (darkMode: boolean) => void
  thumbnailUrl: string
}

const BustPlayPage = (props: BustPlayPageProps) => {
  const {
    bracketMeta,
    setBracketMeta,
    darkMode,
    setDarkMode,
    matchTree,
    setMatchTree,
    bracketPlay: play,
    apparelUrl,
    thumbnailUrl,
  } = props

  const [page, setPage] = useState('view')


  const actionButtonCallback = async () => {
    // set page state to "bust"
    setPage('bust')
  }

  useEffect(() => {
    const picks = play?.picks
    const title = play?.tournament?.title
    const date = 'Sept 2094'
    setBracketMeta({ title, date })
    const template = play?.tournament?.bracketTemplate
    const matches = template?.matches
    const numTeams = template?.numTeams
    if (picks && matches) {
      const tree = MatchTree.fromPicks(numTeams, matches, picks)

      if (tree) {
        setMatchTree(tree)
      }
    }
  }, [play])

  if (page === 'bust' && matchTree) {
    return (
      <BustPlayBuilder
        matchTree={matchTree}
        setMatchTree={setMatchTree}
        bracketPlay={play}
        redirectUrl={apparelUrl}
        />
    )
  }

  if (page === 'bust' && matchTree) {
    return (
      <BustPlayBuilder
        matchTree={matchTree}
        setMatchTree={setMatchTree}
        bracketPlay={play}
        redirectUrl={apparelUrl}
        />
    )
  }

  return (
    <div
      className={`wpbb-reset tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover${
        darkMode ? ' tw-dark' : ''
      }`}
      style={{
        backgroundImage: `url(${darkMode ? darkBracketBg : lightBracketBg})`,
      }}
    >
      <div
        className={`tw-flex tw-flex-col tw-items-center tw-max-w-screen-lg tw-m-auto`}
      >
        {matchTree && (
          <>
            <div className="tw-h-[140px] tw-flex tw-flex-col tw-justify-center tw-items-center">
              <ThemeSelector darkMode={darkMode} setDarkMode={setDarkMode} />
            </div>
            <div className="tw-mb-40 tw-flex tw-flex-col tw-justify-center tw-items-center">
              <img className="tw-h-50 tw-w-50 tw-rounded-full" src={thumbnailUrl} alt="celebrity-photo" />
            </div>
            <PickableBracket matchTree={matchTree} />
            <div className="tw-h-[260px] tw-flex tw-flex-col tw-justify-center tw-items-center">
              <ActionButton
                variant="big-green"
                darkMode={darkMode}
                onClick={actionButtonCallback}
              >
                Bust Bracket
              </ActionButton>
            </div>
          </>
        )}
      </div>
    </div>
  )
}

const WrappedBustPlayPage = WithProvider(
  WithMatchTree(WithBracketMeta(WithDarkMode(BustPlayPage)))
)
export default WrappedBustPlayPage
