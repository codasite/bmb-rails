import React, { useEffect } from 'react'
import { ThemeSelector } from '../../shared/components'
import { MatchTree } from '../../shared/models/MatchTree'
import { PickableBracket } from '../../shared/components/Bracket'
import { ActionButton } from '../../shared/components/ActionButtons'
import {
  WithBracketMeta,
  WithDarkMode,
  WithMatchTree,
  WithProvider,
} from '../../shared/components/HigherOrder'
//@ts-ignore
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
//@ts-ignore
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { BracketMeta } from '../../shared/context'
import { getBracketMeta } from '../../shared/utils'
import { ViewPlayPageProps } from './types'

export const BracketPlayPage = (props: ViewPlayPageProps) => {
  const {
    bracketMeta,
    setBracketMeta,
    darkMode,
    setDarkMode,
    matchTree,
    setMatchTree,
    bracketPlay: play,
    apparelUrl,
  } = props

  useEffect(() => {
    const picks = play?.picks
    const meta = getBracketMeta(play?.bracket)
    setBracketMeta(meta)
    const bracket = play?.bracket?.bracketBracket ?? play?.bracket
    const matches = bracket?.matches
    const numTeams = bracket?.numTeams
    if (picks && matches) {
      const tree = MatchTree.fromPicks(numTeams, matches, picks)
      if (tree) {
        setMatchTree(tree)
      }
    }
  }, [play])

  const handleAddToApparel = () => {
    console.log('handleAddToApparel')
    console.log('apparelUrl', apparelUrl)
    const playId = play?.id
    if (playId) {
      //set play id in cookie
      const expiryDate = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000) // 30 days from now
      document.cookie = `play_id=${playId}; path=/; expires=${expiryDate.toUTCString()}`
    }
    window.location.href = props.apparelUrl
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
            <PickableBracket matchTree={matchTree} />
            <div className="tw-h-[260px] tw-flex tw-flex-col tw-justify-center tw-items-center">
              <ActionButton
                variant="big-green"
                darkMode={darkMode}
                onClick={handleAddToApparel}
              >
                Add to Apparel
              </ActionButton>
            </div>
          </>
        )}
      </div>
    </div>
  )
}
