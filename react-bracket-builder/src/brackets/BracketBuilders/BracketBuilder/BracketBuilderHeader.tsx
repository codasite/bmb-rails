import React from 'react'

export const BracketBuilderHeader = (props: {}) => {
  return (
    <div className="tw-flex tw-flex-col tw-gap-15 tw-py-30 tw-items-center">
      <div className="tw-h-[50px] tw-w-[200px] md:tw-h-[75px] md:tw-w-[300px] lg:tw-h-[85px] lg:tw-w-[340px] tw-bg-[url('https://s3.amazonaws.com/backmybracket.com/bmb_text_logo_500.png')] tw-bg-no-repeat tw-bg-contain" />
      <h1 className="tw-text-30 sm:tw-text-48 md:tw-text-64 lg:tw-text-80 tw-leading-normal tw-text-center">
        Bracket Builder
      </h1>
    </div>
  )
}
