import React, { useState, useEffect } from 'react'
import * as Sentry from '@sentry/react'
import { bracketApi } from '../../shared/api/bracketApi'
import { Nullable } from '../../../utils/types'

import { MatchTree } from '../../shared/models/MatchTree'
import { BracketMeta } from '../../shared/context'
import {
  WithDarkMode,
  WithMatchTree,
  WithBracketMeta,
  WithProvider,
} from '../../shared/components/HigherOrder'
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { PickableBracket } from '../../shared/components/Bracket/PickableBracket'
import { ThemeSelector } from '../../shared/components'
import { ActionButton } from '../../shared/components/ActionButtons'
import { PlayReq } from '../../shared/api/types/bracket'
import { useWindowDimensions } from '../../../utils/hooks'
import { PaginatedPickableBracket } from '../../shared/components/Bracket'
import { PlayBuilderProps } from './types'

export const PlayBuilder = (props: PlayBuilderProps) => {
  const {
    darkMode,
    setDarkMode,
    matchTree,
    setMatchTree,
    handleApparelClick,
    processing,
  } = props

  return (
    <div
      className={`wpbb-reset tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover${
        darkMode ? ' tw-dark' : ''
      }`}
      style={{
        backgroundImage: `url(${darkMode ? darkBracketBg : lightBracketBg})`,
      }}
    >
      {matchTree && (
        <div
          className={`tw-flex tw-flex-col tw-items-center tw-max-w-screen-lg tw-m-auto`}
        >
          <div className="tw-h-[140px] tw-flex tw-flex-col tw-justify-center tw-items-center">
            <ThemeSelector darkMode={darkMode} setDarkMode={setDarkMode} />
          </div>
          <PickableBracket matchTree={matchTree} setMatchTree={setMatchTree} />
          <div className="tw-h-[260px] tw-flex tw-flex-col tw-justify-center tw-items-center">
            <ActionButton
              variant="big-green"
              darkMode={darkMode}
              onClick={handleApparelClick}
              disabled={processing || !matchTree.allPicked()}
            >
              Add to Apparel
            </ActionButton>
          </div>
        </div>
      )}
    </div>
  )
}
