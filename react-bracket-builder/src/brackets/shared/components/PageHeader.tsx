import { H1, H2, H3 } from '../../../elements'

export const PageHeader = (props: { title: string; subtitle?: string }) => {
  return (
    <div className="tw-flex tw-flex-col tw-items-center tw-gap-20">
      <div className="tw-h-[50px] tw-w-[200px] md:tw-h-[75px] md:tw-w-[300px] lg:tw-h-[85px] lg:tw-w-[340px] tw-bg-[url('https://s3.amazonaws.com/backmybracket.com/bmb_text_logo_500.png')] tw-bg-no-repeat tw-bg-contain" />
      {props.title && <H1 className="tw-text-center">{props.title}</H1>}
      {props.subtitle && (
        <H3 className="!tw-text-white/50 tw-text-center">{props.subtitle}</H3>
      )}
    </div>
  )
}
